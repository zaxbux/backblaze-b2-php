<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Operations;

use Zaxbux\BackblazeB2\Exceptions\NotFoundException;
use Zaxbux\BackblazeB2\Http\Endpoint;
use Zaxbux\BackblazeB2\Object\AccountAuthorization;
use Zaxbux\BackblazeB2\Object\File;
use Zaxbux\BackblazeB2\Object\File\ServerSideEncryption;
use Zaxbux\BackblazeB2\Response\FileList;
use Zaxbux\BackblazeB2\Utils;

/** @package BackblazeB2\Operations */
trait FileOperationsTrait
{
	/** @var \Zaxbux\BackblazeB2\Config */
	protected $config;

	/** @var \GuzzleHttp\ClientInterface */
	protected $http;

	abstract protected function accountAuthorization(): AccountAuthorization;

	/**
	 * Creates a new file by copying from an existing file.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_copy_file.html
	 * 
	 * @b2-capability writeFiles
	 * @b2-capability [readFiles] If the bucket is private.
	 * @b2-transaction Class C
	 * 
	 * @param string $sourceFileId                  The ID of the source file being copied.
	 * @param string $fileName                      The name of the new file being created.
	 * @param string $destinationBucketId           The ID of the bucket where the copied file will be stored.
	 * @param string $range                         The range of bytes to copy. Otherwise, the whole source file will be copied.
	 * @param string $metadataDirective             The strategy for how to populate metadata for the new file.
	 * @param string $contentType                   Must only be supplied if the *metadataDirective* is `REPLACE`. Use the
	 *                                              `b2/x-auto` MIME type to automatically set the stored Content-Type post
	 *                                              upload.
	 * @param array $fileInfo                       Must only be supplied if the *metadataDirective* is `REPLACE`. This field stores
	 *                                              the metadata that will be stored with the file.
	 * @param array $fileRetention                  The File Lock retention settings for the new file.
	 * @param array $legalHold                      The File Lock legal hold status for the new file.
	 * @param ServerSideEncryption $sourceSSE       The parameters for B2 to decrypt the source file.
	 * @param ServerSideEncryption $destinationSSE  The parameters for B2 to encrypt the copied data before storing the destination file.
	 */
	public function copyFile(
		string $sourceFileId,
		string $fileName,
		?string $destinationBucketId = null,
		?string $range = null,
		?string $metadataDirective = null,
		?string $contentType = null,
		?array $fileInfo = null,
		?array $fileRetention = null,
		?array $legalHold = null,
		?ServerSideEncryption $sourceSSE = null,
		?ServerSideEncryption $destinationSSE = null
	): File {
		$response = $this->http->request('POST', Endpoint::COPY_FILE, [
			'json' => Utils::filterRequestOptions([
				File::ATTRIBUTE_SOURCE_FILE_ID => $sourceFileId,
				File::ATTRIBUTE_FILE_NAME      => $fileName,
			], [
				File::ATTRIBUTE_DESTINATION_BUCKET_ID => $destinationBucketId,
				File::ATTRIBUTE_RANGE                 => $range,
				File::ATTRIBUTE_METADATA_DIRECTIVE    => $metadataDirective,
				File::ATTRIBUTE_CONTENT_TYPE          => $contentType,
				File::ATTRIBUTE_FILE_INFO             => $fileInfo,
				File::ATTRIBUTE_FILE_RETENTION        => $fileRetention,
				File::ATTRIBUTE_LEGAL_HOLD            => $legalHold,
				File::ATTRIBUTE_SOURCE_SSE            => $sourceSSE ? $sourceSSE->toArray() : null,
				File::ATTRIBUTE_DESTINATION_SSE       => $destinationSSE ? $destinationSSE->toArray() : null,
			])
		]);

		return File::fromResponse($response);
	}

	/**
	 * Deletes one version of a file.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_delete_file_version.html
	 * 
	 * @b2-capability deleteFiles
	 * @b2-transaction Class A
	 *
	 * @param string $fileName         The name of the file.
	 * @param string $fileId           The ID of the file.
	 * @param bool   $bypassGovernance Must be specified and set to true if deleting a file version protected by
	 *                                 File Lock governance mode retention settings.
	 */
	public function deleteFileVersion(
		string $fileId,
		?string $fileName =  null,
		?bool $bypassGovernance = false
	): File {
		$response = $this->http->request('POST', Endpoint::DELETE_FILE_VERSION, [
			'json' => Utils::filterRequestOptions([
				File::ATTRIBUTE_FILE_ID   => $fileId,
				File::ATTRIBUTE_FILE_NAME => $fileName ?? $this->getFileById($fileId)->id(),
			], [
				File::ATTRIBUTE_BYPASS_GOVERNANCE => $bypassGovernance,
			]),
		]);

		return File::fromResponse($response);
	}

	/**
	 * Gets information about one file stored in B2.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_get_file_info.html
	 * 
	 * @b2-capability readFiles
	 * @b2-transaction Class B
	 *
	 * @param string $fileId The ID of the file.
	 */
	public function getFileInfo(string $fileId): File
	{
		$response = $this->http->request('POST', Endpoint::GET_FILE_INFO, [
			'json' => [
				File::ATTRIBUTE_FILE_ID => $fileId
			]
		]);

		return File::fromResponse($response);
	}

	/**
	 * Hides a file so that downloading by name will not find the file,
	 * but previous versions of the file are still stored.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_hide_file.html
	 * 
	 * @b2-capability writeFiles
	 * @b2-transaction Class A
	 * 
	 * @param string $bucketId
	 * @param string $fileName
	 */
	public function hideFile(string $fileName, ?string $bucketId = null): File
	{

		$response = $this->http->request('POST', Endpoint::HIDE_FILE, [
			'json' => [
				File::ATTRIBUTE_BUCKET_ID => $bucketId ?? $this->allowedBucketId(),
				File::ATTRIBUTE_FILE_NAME => $fileName,
			]
		]);

		return File::fromResponse($response);
	}

	/**
	 * Lists the names of all files in a bucket, starting at a given name.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_list_file_names.html
	 * 
	 * @b2-capability listFiles
	 * @b2-transaction Class C
	 *
	 * @param string $bucketId      The bucket to look for file names in.
	 * @param string $prefix        Files returned will be limited to those with the given prefix. Defaults to the
	 *                              empty string, which matches all files. If not, the first file name after this the
	 *                              first one after this name. 
	 * @param string $delimiter     Files returned will be limited to those within the top folder, or any one
	 *                              subfolder. Folder names will also be returned.
	 *                              The delimiter character will be used to "break" file names into folders.
	 * @param string $startFileName The first file name to return. If there is a file with this name, it will be
	 *                              returned in the list.
	 * @param int    $maxFileCount  The maximum number of files to return from this call. The default value is 100, and
	 *                              the maximum is 10000.
	 */
	public function listFileNames(
		?string $bucketId = null,
		?string $prefix = null,
		?string $delimiter = null,
		?string $startFileName = null,
		?int $maxFileCount = null
	): FileList {
		$response = $this->http->request('POST', Endpoint::LIST_FILE_NAMES, [
			'json' => Utils::filterRequestOptions([
				File::ATTRIBUTE_BUCKET_ID      => $bucketId ?? $this->allowedBucketId(),
				File::ATTRIBUTE_MAX_FILE_COUNT => $maxFileCount ?? $this->config->maxFileCount(),
			], [
				File::ATTRIBUTE_PREFIX => $prefix,
				File::ATTRIBUTE_DELIMITER => $delimiter,
				File::ATTRIBUTE_START_FILE_NAME => $startFileName,
			]),
		]);

		return FileList::fromResponse($response);
	}

	/**
	 * Lists all of the versions of all of the files contained in one bucket, in alphabetical order by file name, and
	 * by reverse of date/time uploaded for versions of files with the same name. 
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_list_file_versions.html
	 * 
	 * @b2-capability listFiles
	 * @b2-transaction Class C
	 * 
	 * @param string $bucketId      The bucket to look for file names in.
	 * @param string $prefix        Files returned will be limited to those with the given prefix. Defaults to the
	 *                              empty string, which matches all files. If not, the first file name after this the
	 *                              first one after this name. 
	 * @param string $delimiter     Files returned will be limited to those within the top folder, or any one
	 *                              subfolder. Folder names will also be returned.
	 *                              The delimiter character will be used to "break" file names into folders.
	 * @param string $startFileName The first file name to return. If there is a file with this name, it will be
	 *                              returned in the list.
	 * @param string $startFileId   
	 * @param int    $maxFileCount  The maximum number of files to return from this call. The default value is 1000, and
	 *                              the maximum is 10000. The maximum number of files returned per transaction is 1000.
	 *                              If more than 1000 are returned, the call will be billed as multiple transactions.
	 */
	public function listFileVersions(
		?string $bucketId = null,
		?string $prefix = '',
		?string $delimiter = null,
		?string $startFileName = null,
		?string $startFileId = null,
		?int $maxFileCount = null
	): FileList {
		$response = $this->http->request('POST', Endpoint::LIST_FILE_VERSIONS, [
			'json' => Utils::filterRequestOptions([
				File::ATTRIBUTE_BUCKET_ID      => $bucketId ?? $this->allowedBucketId(),
			], [
				File::ATTRIBUTE_START_FILE_NAME => $startFileName,
				File::ATTRIBUTE_START_FILE_ID   => $startFileId,
				File::ATTRIBUTE_MAX_FILE_COUNT  => $maxFileCount ?? $this->config->maxFileCount(),
				File::ATTRIBUTE_PREFIX          => $prefix,
				File::ATTRIBUTE_DELIMITER       => $delimiter,
			]),
		]);

		return FileList::fromResponse($response);
	}

	/**
	 * Update the File Lock legal hold status for an existing file.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_update_file_legal_hold.html
	 * 
	 * @b2-capability writeFileLegalHolds
	 * @b2-transaction Class A
	 * 
	 * @param string $fileName  The name of the file.
	 * @param string $fileId    The ID of the file.
	 * @param string $legalHold The legal hold status of the file. `on` = *enabled*; `off` = *disabled*.
	 */
	public function updateFileLegalHold(
		string $fileId,
		?string $fileName = null,
		string $legalHold
	): File {
		$response = $this->http->request('POST', Endpoint::UPDATE_FILE_LEGAL_HOLD, [
			'json' => Utils::filterRequestOptions([
				File::ATTRIBUTE_FILE_NAME  => $fileName ?? $this->getFileById($fileId)->name(),
				File::ATTRIBUTE_FILE_ID    => $fileId,
				File::ATTRIBUTE_LEGAL_HOLD => $legalHold,
			]),
		]);

		return File::fromResponse($response);
	}

	/**
	 *  Update the File Lock retention settings for an existing file.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_update_file_retention.html
	 * 
	 * @b2-capability writeFileRetentions
	 * @b2-transaction Class A
	 * 
	 * @param string $fileName         The name of the file.
	 * @param string $fileId           The ID of the file.
	 * @param array $fileRetention     The file retention settings.
	 * @param bool   $bypassGovernance To shorten or remove an existing governance mode retention setting,
	 *                                 this must also be specified and set to `true`.
	 */
	public function updateFileRetention(
		string $fileId,
		?string $fileName = null,
		array $fileRetention,
		?bool $bypassGovernance = false
	): File {
		$response = $this->http->request('POST', Endpoint::UPDATE_FILE_RETENTION, [
			'json' => Utils::filterRequestOptions([
				File::ATTRIBUTE_FILE_NAME         => $fileName ?? $this->getFileById($fileId)->id(),
				File::ATTRIBUTE_FILE_ID           => $fileId,
				File::ATTRIBUTE_FILE_RETENTION    => $fileRetention,
				File::ATTRIBUTE_BYPASS_GOVERNANCE => $bypassGovernance,
			]),
		]);

		return File::fromResponse($response);
	}

	/**
	 * Fetch details of all files in a bucket, with optional filters.
	 * 
	 * @see Client::listFileNames()
	 * 
	 * @return iterable<File>
	 */
	public function listAllFileNames(
		?string $bucketId = null,
		string $prefix = '',
		string $delimiter = null,
		string $startFileName = null
	): FileList {
		$allFiles = new FileList();
		$nextFileName = $startFileName ?? '';

		while ($nextFileName !== null) {
			$response     = $this->listFileNames($bucketId, $prefix, $delimiter, $startFileName);
			$nextFileName = $response->nextFileName();

			$allFiles->mergeList($response);
		}

		return $allFiles;
	}

	public function getFileByName(string $fileName, ?string $bucketId = null): File
	{
		if (!$file = $this->listFileNames($bucketId, '', null, $fileName, 1)->current()) {
			throw new NotFoundException(sprintf('No results returned for file name "%s"', $fileName));
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
		?string $bucketId = null,
		?string $prefix = '',
		?string $delimiter = null,
		?string $startFileName = null,
		?string $startFileId = null
	): FileList {
		$allFiles = new FileList();
		$nextFileId = $startFileId ?? '';
		$nextFileName = $startFileName ?? '';

		while ($nextFileId !== null && $nextFileName !== null) {
			$response     = $this->listFileVersions($bucketId, $prefix, $delimiter, $startFileName, $startFileId);
			$nextFileId   = $response->nextFileId();
			$nextFileName = $response->nextFileName();

			$allFiles->mergeList($response);
		}

		return $allFiles;
	}

	public function getFileById(string $fileId, ?string $bucketId = null): File
	{
		if (!$file = $this->listFileVersions($bucketId, '', null, null, $fileId, 1)->current()) {
			throw new NotFoundException(sprintf('No results returned for file id "%s"', $fileId));
		}

		return $file;
	}

	/**
	 * Deletes all versions of a file(s) in a bucket.
	 * 
	 * @see FileService::deleteFileVersion()
	 * 
	 * @param string      $bucketId         The ID of the bucket containing file versions to delete.
	 * @param null|string $prefix           
	 * @param null|string $delimiter        
	 * @param null|string $startFileName    
	 * @param null|string $startFileId      
	 * @param null|bool   $bypassGovernance 
	 */
	public function deleteAllFileVersions(
		?string $startFileId = null,
		?string $startFileName = null,
		?string $prefix = '',
		?string $delimiter = null,
		?string $bucketId = null,
		?bool $bypassGovernance = false
	): FileList {
		$fileVersions = $this->listAllFileVersions($bucketId, $prefix, $delimiter, $startFileName, $startFileId);

		$deleted = new FileList();

		while ($fileVersions->valid()) {

			$deleted->append($this->deleteFileVersion(
				$fileVersions->current()->name(),
				$fileVersions->current()->id(),
				$bypassGovernance
			));

			$fileVersions->next();
		}

		return $deleted;
	}
}
