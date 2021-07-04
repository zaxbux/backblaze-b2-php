<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Traits;

use function is_string;

use AppendIterator;
use NoRewindIterator;
use Psr\Http\Message\StreamInterface;
use Zaxbux\BackblazeB2\Client;
use Zaxbux\BackblazeB2\B2\Object\File;
use Zaxbux\BackblazeB2\B2\Response\DownloadResponse;
use Zaxbux\BackblazeB2\B2\Response\FileListResponse;
use Zaxbux\BackblazeB2\B2\Response\FilePartListResponse;
use Zaxbux\BackblazeB2\Classes\DownloadOptions;
use Zaxbux\BackblazeB2\Client\Exception\NotFoundException;
use Zaxbux\BackblazeB2\Client\Exception\ValidationException;

trait FileServiceHelpersTrait
{
	/*******************************************************\
	|  Abstract signatures for methods used by this trait.  |
	\*******************************************************/

	/** @var Client */
	protected $client;

	public abstract function deleteFileVersion(
		string $fileName,
		string $fileId,
		?bool $bypassGovernance = false
	): File;

	public abstract function listFileNames(
		string $bucketId,
		?string $prefix = '',
		?string $delimiter = null,
		?string $startFileName = null
	): FileListResponse;

	public abstract function listFileVersions(
		string $bucketId,
		?string $prefix = '',
		?string $delimiter = null,
		?string $startFileName = null,
		?string $startFileId = null
	): FileListResponse;

	public abstract function listParts(
		string $fileId,
		?int $startPartNumber = null
	): FilePartListResponse;

	public abstract function listUnfinishedLargeFiles(
		string $bucketId,
		?string $namePrefix,
		?string $startFileId,
		?int $maxFileCount
	): FileListResponse;

	/* End abstract methods */

	/**
	 * Deletes all versions of a file in a bucket.
	 * 
	 * @see Client::deleteFileVersion()
	 * 
	 * @param string $bucketId 
	 * @param string $fileName 
	 * @param null|bool $bypassGovernance 
	 */
	public function deleteAllFileVersions(string $bucketId, string $fileName, ?bool $bypassGovernance = false): void
	{
		$fileVersions = $this->listAllFileVersions($bucketId, null, null, $fileName);

		foreach ($fileVersions as $version) {
			$this->deleteFileVersion($version->getName(), $version->getId(), $bypassGovernance);
		}
	}

	/**
	 * Fetch details of all files in a bucket, with optional filters.
	 * 
	 * @see Client::listFileNames()
	 * 
	 * @return iterable<File>
	 */
	public function listAllFileNames(
		string $bucketId,
		string $prefix = '',
		string $delimiter = null,
		string $startFileName = null,
		int $maxFileCount = 1000
	): iterable {
		$files = [];

		while (true) {
			$response = $this->listFileNames($bucketId, $prefix, $delimiter, $startFileName, $maxFileCount);

			array_merge($files, $response['files']);
			$startFileName = $response['nextFileName'];

			if ($response['nextFileName'] == null) {
				break;
			}
		}

		return [
			'files'        => $files,
			'nextFileName' => null,
		];
	}

	public function getFileByName(string $bucketId, string $fileName): File
	{
		$files = $this->listFileNames($bucketId, '', null, $fileName, 1);

		if (iterator_count($files->getFiles()) < 1) {
			throw new NotFoundException();
		}

		return File::fromArray($files->getFiles()[0]);
	}

	/**
	 * Fetch details of all versions of all files in a bucket, with optional filters.
	 * 
	 * @see Client::listFileVersions()
	 * 
	 * @return iterable<File>
	 * @throws ValidationException 
	 */
	public function listAllFileVersions(
		string $bucketId,
		?string $prefix = '',
		?string $delimiter = null,
		?string $startFileName = null,
		?string $startFileId = null
	): iterable {
		if ($startFileId && !$startFileName) {
			throw new ValidationException('$startFileName is required if $startFileId is provided.');
		}

		$allFiles = new AppendIterator();
		$nextFileId = $startFileId;
		$nextFileName = $startFileName;

		while ($nextFileId !== null && $nextFileName !== null) {
			$files        = $this->listFileVersions($bucketId, $prefix, $delimiter, $startFileName, $startFileId);
			$nextFileId   = $files->getNextFileId();
			$nextFileName = $files->getNextFileName();

			$allFiles->append(new NoRewindIterator($files->getFiles()));
		}

		return $allFiles;
	}

	public function getFileById(string $bucketId, string $fileId): File
	{
		$files = $this->listFileVersions($bucketId, '', null, null, $fileId, 1);

		if (iterator_count($files->getFiles()) < 1) {
			throw new NotFoundException();
		}

		return File::fromArray($files->getFiles()[0]);
	}

	/**
	 * Internal method to call the b2_list_parts API
	 * 
	 * @see Client::listParts()
	 * 
	 * @return iterable<File>
	 */
	public function listAllParts(
		string $fileId,
		int $startPartNumber = null
	): iterable {
		$allParts = new AppendIterator();
		$nextPartNumber = $startPartNumber ?? 0;

		while ($nextPartNumber !== null) {
			$parts          = $this->listParts($fileId, $startPartNumber);
			$nextPartNumber = $parts->getNextPartNumber();

			$allParts->append(new NoRewindIterator($parts->getParts()));
		}

		return $allParts;
	}

	/**
	 * Lists information about *all* large file uploads that have been started,
	 * but that have not been finished or canceled.
	 * 
	 * @see Client::listUnfinishedLargeFiles()
	 * 
	 * @return iterable<File>
	 */
	public function listAllUnfinishedLargeFiles(
		string $bucketId,
		string $namePrefix = null,
		string $startFileId = null,
		int $maxFileCount = 100
	): iterable {

		$allFiles = new AppendIterator();
		$nextFileId = $startFileId ?? '';

		while ($nextFileId !== null) {
			$files = $this->listUnfinishedLargeFiles($bucketId, $namePrefix, $startFileId, $maxFileCount);
			$nextFileId = $files->getNextFileId();

			$allFiles->append(new NoRewindIterator($files->getFiles()));
		}

		return $allFiles;
	}

	/**
	 * Internal method to save/stream files.
	 * 
	 * @param string                 $downloadUrl The URL to make the request to.
	 * @param array                  $query       Query parameters.
	 * @param DownloadOptions|array  $options     Additional options for the B2 API.
	 * @param string|resource        $sink        A string, stream, or StreamInterface that specifies where to save the file.
	 * @param bool                   $headersOnly Only get the file headers, without downloading the whole file.
	 */
	protected function download(
		string $downloadUrl,
		?array $query = null,
		$options = null,
		$sink = null,
		?bool $headersOnly = false
	): DownloadResponse {
		if (! $options instanceof DownloadOptions) {
			/** @var DownloadOptions */
			$options = DownloadOptions::fromArray($options ?? []);
		}

		// Build query string from query parameters and download options.
		$queryString = join('&', [http_build_query($query ?? []), $options->toQueryString() ?? []]);

		$response = $this->guzzle->request($headersOnly ? 'HEAD' :'GET', $downloadUrl, [
			'query'   => $queryString,
			'headers' => $options->getHeaders(),
			'sink'    => $sink ?: null,
			'stream'  => static::isStream($sink),
		]);

		return DownloadResponse::create($response, !is_string($sink) ?: $sink);
	}

	private static function isStream($var): bool
	{
		return !is_string($var) && (is_resource($var) || $var instanceof StreamInterface);
	}
}
