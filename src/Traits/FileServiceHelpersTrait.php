<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Traits;

use function sprintf, is_string;

use AppendIterator;
use ArrayIterator;
use Iterator;
use NoRewindIterator;
use RuntimeException;
use Psr\Http\Message\StreamInterface;
use Zaxbux\BackblazeB2\Response\FileList;
use Zaxbux\BackblazeB2\Response\FilePartList;
use Zaxbux\BackblazeB2\Object\File\DownloadOptions;
use Zaxbux\BackblazeB2\Object\File\FileUploadMetadata;
use Zaxbux\BackblazeB2\Client;
use Zaxbux\BackblazeB2\Exceptions\NotFoundException;
use Zaxbux\BackblazeB2\Helpers\LargeFileUpload;
use Zaxbux\BackblazeB2\Object\File;
use Zaxbux\BackblazeB2\Object\File\FileInfo;
use Zaxbux\BackblazeB2\Object\File\ServerSideEncryption;
use Zaxbux\BackblazeB2\Object\File\UploadUrl;
use Zaxbux\BackblazeB2\Response\FileDownload;

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
		?string $startFileName = null,
		?int $maxFileCount = 1000
	): FileList;

	/*public abstract function listFileVersions(
		string $bucketId,
		?string $prefix = '',
		?string $delimiter = null,
		?string $startFileName = null,
		?string $startFileId = null,
		?int $maxFileCount = 1000
	): FileList;*/

	public abstract function listParts(
		string $fileId,
		?int $startPartNumber = null
	): FilePartList;

	public abstract function listUnfinishedLargeFiles(
		string $bucketId,
		?string $namePrefix,
		?string $startFileId,
		?int $maxFileCount
	): FileList;

	/* End abstract methods */

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
	): Iterator {
		$allFiles = new AppendIterator();
		$nextFileName = $startFileName ?? '';

		while ($nextFileName !== null) {
			$response     = $this->listFileNames($bucketId, $prefix, $delimiter, $startFileName, $maxFileCount);
			$nextFileName = $response->getNextFileName();

			$allFiles->append(new NoRewindIterator($response->getFiles()));
		}

		return $allFiles;
	}

	public function getFileByName(string $bucketId, string $fileName): File
	{
		if (!$file = $this->listFileNames($bucketId, '', null, $fileName, 1)->first()) {
			throw new NotFoundException(sprintf('No results returned for file name "%s"'));
		}

		return $file;
	}

	/**
	 * Fetch details of all versions of all files in a bucket, with optional filters.
	 * 
	 * @see Client::listFileVersions()
	 * 
	 * @return iterable<File>
	 */
	public function listAllFileVersions(
		string $bucketId,
		?string $prefix = '',
		?string $delimiter = null,
		?string $startFileName = null,
		?string $startFileId = null
	): Iterator {
		$allFiles = new AppendIterator();
		$nextFileId = $startFileId ?? '';
		$nextFileName = $startFileName ?? '';

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
		if (!$file = $this->listFileVersions($bucketId, '', null, null, $fileId, 1)->first()) {
			throw new NotFoundException(sprintf('No results returned for file id "%s"'));
		}

		return $file;
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
	): FileDownload {
		if (!$options instanceof DownloadOptions) {
			/** @var DownloadOptions */
			$options = DownloadOptions::fromArray($options ?? []);
		}

		// Build query string from query parameters and download options.
		$queryString = implode('&', [http_build_query($query ?? []), $options->toQueryString() ?? []]);

		$response = $this->config->client()->request($headersOnly ? 'HEAD' : 'GET', $downloadUrl, [
			'query'   => $queryString,
			'headers' => $options->getHeaders(),
			'sink'    => $sink ?? null,
			'stream'  => static::isStream($sink),
		]);

		return FileDownload::create($response, !is_string($sink) ? null : $sink);
	}

	/**
	 * Upload a file. Automatically decides the upload method (regular or large file).
	 *
	 * @param string|UploadUrl          $bucketIdOrUploadUrl  Must be a bucket ID for large files.
	 * @param string                    $fileName             
	 * @param string|resource           $stream               
	 * @param null|string               $contentType          
	 * @param null|FileInfo             $fileInfo             
	 * @param null|ServerSideEncryption $serverSideEncryption 
	 * @return File 
	 */
	public function uploadStream(
		$bucketIdOrUploadUrl,
		string $fileName,
		$stream,
		?string $contentType = null,
		?FileInfo $fileInfo = null,
		?array $fileRetention,
		?bool $legalHold,
		?ServerSideEncryption $serverSideEncryption = null
	): File {
		$bucketId = $bucketIdOrUploadUrl;

		if ($bucketIdOrUploadUrl instanceof UploadUrl) {
			// Get bucket ID from Upload URL object.
			$bucketId = $bucketIdOrUploadUrl->getBucketId();
		} else {
			// Set null to force fetching an Upload URL.
			$bucketIdOrUploadUrl = null;
		}

		$metadata = FileUploadMetadata::fromResource($stream);

		if ($metadata->getLength() < File::SINGLE_FILE_MIN_SIZE || $metadata->getLength() > File::LARGE_FILE_MAX_SIZE) {
			throw new RuntimeException(sprintf(
				'Upload size is not between %d bytes and %d bytes.',
				File::SINGLE_FILE_MIN_SIZE,
				File::LARGE_FILE_MAX_SIZE
			));
		}

		// Upload as large file if greater than single file size or configured size,
		// and greater than the minimum part size for the account.
		if (($metadata->getLength() > File::SINGLE_FILE_MAX_SIZE ||
			$metadata->getLength() > 200 * 1024 * 1024) &&
			$metadata->getLength() > $this->accountAuthorization->getAbsoluteMinimumPartSize()
		) {
			// Upload large file
			return $this->uploadLargeFile(
				$bucketId,
				$fileName,
				$stream,
				$contentType,
				$fileInfo,
				$legalHold,
				$fileRetention,
				$serverSideEncryption,
				$metadata
			);
		}

		// Upload as regular file
		return $this->uploadFile(
			$bucketId,
			$fileName,
			$stream,
			$contentType,
			$fileInfo,
			$fileRetention,
			$legalHold,
			$serverSideEncryption,
			$bucketIdOrUploadUrl
		);
	}

	/**
	 * @see FileServiceHelpersTrait::uploadStream()
	 * 
	 * @param string|UploadUrl          $bucketIdOrUploadUrl  
	 * @param string                    $fileName             
	 * @param string|resource           $filePath             The path to the file.
	 * @param null|string               $contentType          
	 * @param null|FileInfo             $fileInfo             
	 * @param null|ServerSideEncryption $serverSideEncryption 
	 * 
	 * @throws RuntimeException
	 */
	public function upload(
		$bucketIdOrUploadUrl,
		string $fileName,
		string $filePath,
		?string $contentType = null,
		?FileInfo $fileInfo = null,
		?array $fileRetention,
		?bool $legalHold,
		?ServerSideEncryption $serverSideEncryption = null
	): File {
		if (!is_file($filePath)) {
			throw new RuntimeException('File does not exist or is not a file.');
		}

		$handle = fopen($filePath, 'rb');

		if (!$handle) {
			throw new RuntimeException('Failure opening file pointer.');
		}

		$file = $this->uploadStream(
			$bucketIdOrUploadUrl, 
			$fileName, 
			$handle, 
			$contentType, 
			$fileInfo, 
			$fileRetention,
			$legalHold,
			$serverSideEncryption
		);

		fclose($handle);

		return $file;
	}

	/**
	 * Helper method that implements the entire large file upload process.
	 * 
	 * @param string                    $bucketId 
	 * @param string                    $fileName             
	 * @param string|resource           $stream               
	 * @param null|string               $contentType          
	 * @param null|FileInfo             $fileInfo             
	 * @param null|ServerSideEncryption $serverSideEncryption 
	 * @param null|UploadMetadata       $metadata             
	 * 
	 * @throws RuntimeException
	 */
	public function uploadLargeFile(
		string $bucketId,
		string $fileName,
		$stream,
		?string $contentType = null,
		?FileInfo $fileInfo = null,
		?array $fileRetention,
		?bool $legalHold,
		?ServerSideEncryption $serverSideEncryption = null,
		?FileUploadMetadata $metadata = null
	): File {
		$largeFileUpload = LargeFileUpload::create($this)->withStream($stream, $fileName);

		if ($contentType) {
			$largeFileUpload->useContentType($contentType);
		}

		if ($fileInfo) {
			$largeFileUpload->useFileInfo($fileInfo);
		}

		if ($fileRetention) {
			$largeFileUpload->withFileRetention($fileRetention);
		}

		if ($legalHold) {
			$largeFileUpload->legalHold($legalHold);
		}

		if ($serverSideEncryption) {
			$largeFileUpload->withEncryption($serverSideEncryption);
		}

		if ($metadata) {
			$largeFileUpload->useFileMetadata($metadata);
		}

		return $largeFileUpload->start($bucketId)->uploadParts()->finish()->getFile();
	}

	private static function isStream($var): bool
	{
		return !is_string($var) && (is_resource($var) || $var instanceof StreamInterface);
	}
}
