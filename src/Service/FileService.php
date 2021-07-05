<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Service;

use Zaxbux\BackblazeB2\Client;
use Zaxbux\BackblazeB2\Object\File;
use Zaxbux\BackblazeB2\Object\DownloadAuthorization;
use Zaxbux\BackblazeB2\Object\File\DownloadOptions;
use Zaxbux\BackblazeB2\Object\File\FileUploadMetadata;
use Zaxbux\BackblazeB2\Object\File\FileInfo;
use Zaxbux\BackblazeB2\Object\File\ServerSideEncryption;
use Zaxbux\BackblazeB2\Object\File\UploadPartUrl;
use Zaxbux\BackblazeB2\Object\File\UploadUrl;
use Zaxbux\BackblazeB2\Response\FileDownload;
use Zaxbux\BackblazeB2\Response\FileList;
use Zaxbux\BackblazeB2\Response\FilePartList;
use Zaxbux\BackblazeB2\Traits\FileServiceHelpersTrait;

trait FileService
{
	use FileServiceHelpersTrait;

	/**
	 * Cancel the upload of a large file, and deletes all of the parts that have been uploaded.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_cancel_large_file.html
	 * 
	 * @param string $fileId The ID returned by `b2_start_large_file`.
	 */
	public function cancelLargeFile(string $fileId)
	{
		$response = $this->guzzle->request('POST', '/b2_cancel_large_file', [
			'json' => [
				File::ATTRIBUTE_FILE_ID => $fileId,
			],
		]);

		return File::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Creates a new file by copying from an existing file.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_copy_file.html
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

		/*
		if ($metadataDirective) {
			if ($metadataDirective == File::METADATA_DIRECTIVE_REPLACE && $contentType == null) {
				$contentType = File::CONTENT_TYPE_AUTO;
			}

			if ($contentType && $metadataDirective !== File::METADATA_DIRECTIVE_REPLACE) {
				throw new InvalidArgumentException(sprintf(
					'%s must not be set when %s is not "%s".',
					File::ATTRIBUTE_CONTENT_TYPE,
					File::ATTRIBUTE_METADATA_DIRECTIVE,
					File::METADATA_DIRECTIVE_REPLACE
				));
			}

			if ($fileInfo && $metadataDirective !== File::METADATA_DIRECTIVE_REPLACE) {
				throw new InvalidArgumentException(sprintf(
					'%s must not be set when %s is not "%s".',
					File::ATTRIBUTE_FILE_INFO,
					File::ATTRIBUTE_METADATA_DIRECTIVE,
					File::METADATA_DIRECTIVE_REPLACE
				));
			}	
		}
		*/

		$response = $this->guzzle->request('POST', '/b2_copy_file', [
			'json' => AbstractService::filterRequestOptions([
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

		return File::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Copies from an existing B2 file, storing it as a part of a large file which has already been started with
	 * `b2_start_large_file`.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_copy_part.html
	 * 
	 * @param string $sourceFileId   The ID of the source file being copied.
	 * @param string $largeFileId    The ID of the large file the part will belong to.
	 * @param int    $partNumber     A number from `1` to `10000`. The parts uploaded for one file must have
	 *                               contiguous numbers, starting with `1`.
	 * @param string $range          The range of bytes to copy.
	 *                               If not provided, the whole source file will be copied.
	 * @param array  $sourceSSE      Specifies the parameters for B2 to use for accessing the
	 *                               source file data using Server-Side Encryption.
	 * @param array  $destinationSSE Specifies the parameters for B2 to use for encrypting the
	 *                               copied data before storing the destination file using Server-Side Encryption.
	 */
	public function copyPart(
		string $sourceFileId,
		string $largeFileId,
		int $partNumber,
		?string $range = null,
		?ServerSideEncryption $sourceSSE = null,
		?ServerSideEncryption $destinationSSE = null
	): File {
		$response = $this->guzzle->request('POST', '/b2_copy_part', [
			'json' => AbstractService::filterRequestOptions([
				File::ATTRIBUTE_SOURCE_FILE_ID => $sourceFileId,
				File::ATTRIBUTE_LARGE_FILE_ID  => $largeFileId,
				File::ATTRIBUTE_PART_NUMBER    => $partNumber,
			], [
				File::ATTRIBUTE_RANGE           => $range,
				File::ATTRIBUTE_SOURCE_SSE      => $sourceSSE,
				File::ATTRIBUTE_DESTINATION_SSE => $destinationSSE,
			]),
		]);

		return File::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Deletes one version of a file.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_delete_file_version.html
	 *
	 * @param string $fileName         The name of the file.
	 * @param string $fileId           The ID of the file.
	 * @param bool   $bypassGovernance Must be specified and set to true if deleting a file version protected by
	 *                                 File Lock governance mode retention settings.
	 */
	public function deleteFileVersion(string $fileName, string $fileId, ?bool $bypassGovernance = false): File
	{
		$response = $this->guzzle->request('POST', '/b2_delete_file_version', [
			'json' => AbstractService::filterRequestOptions([
				File::ATTRIBUTE_FILE_NAME => $fileName,
				File::ATTRIBUTE_FILE_ID   => $fileId,
			], [
				File::ATTRIBUTE_BYPASS_GOVERNANCE => $bypassGovernance,
			]),
		]);

		return File::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Downloads one file from B2 by File ID.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_download_file_by_id.html
	 * 
	 * @param string                $fileId      The file ID to download.
	 * @param DownloadOptions|array $options     An optional array of additional B2 API options.
	 * @param string                $range       A standard RFC 7233 byte-range request, that will only return part of the stored file.
	 * @param string|resource       $sink        A string, stream, or `StreamInterface` that specifies where to save the file.
	 *                                           {@link https://docs.guzzlephp.org/en/stable/request-options.html#sink}
	 * @param bool                  $headersOnly Only get the file headers, without downloading the whole file.
	 */
	public function downloadFileById(
		string $fileId,
		$options = null,
		$sink = null,
		?bool $headersOnly = false
	): FileDownload {
		$downloadUrl = sprintf('%s/b2_download_file_by_id', $this->accountAuthorization->getDownloadUrl() . Client::B2_API_V2);

		return $this->download($downloadUrl, [File::ATTRIBUTE_FILE_ID => $fileId], $options, $sink, $headersOnly);
	}

	/**
	 * Downloads one file from B2 by File Name.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_download_file_by_name.html
	 * 
	 * @param string                 $fileName    The file name to download.
	 * @param string                 $bucketName  The bucket the file is contained in.
	 * @param DownloadOptions|array  $options     An optional array of additional B2 API options.
	 * @param string                 $range       A standard RFC 7233 byte-range request, that will only return part of the stored file.
	 * @param string|resource        $sink        A string, stream, or `StreamInterface` that specifies where to save the file.
	 *                                            {@link https://docs.guzzlephp.org/en/stable/request-options.html#sink}
	 * @param bool                   $headersOnly Only get the file headers, without downloading the whole file.
	 */
	public function downloadFileByName(
		string $fileName,
		string $bucketName,
		$options = null,
		$sink = null,
		?bool $headersOnly = false
	): FileDownload {
		$downloadUrl = sprintf('%s/file/%s/%s', $this->accountAuthorization->getApiUrl(), $bucketName, $fileName);

		return $this->download($downloadUrl, null, $options, $sink, $headersOnly);
	}

	/**
	 * Converts the parts that have been uploaded into a single B2 file.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_finish_large_file.html
	 * 
	 * @param string   $fileId The ID of the large file.
	 * @param string[] $hashes An array of SHA1 checksums of the parts of the large file.
	 * 
	 * @return File
	 */
	public function finishLargeFile(string $fileId, array $hashes)
	{
		$response = $this->guzzle->request('POST', '/b2_finish_large_file', [
			'json' => [
				File::ATTRIBUTE_FILE_ID         => $fileId,
				File::ATTRIBUTE_PART_SHA1_ARRAY => $hashes,
			]
		]);

		return File::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Generates an authorization token that can be used to download files
	 * with the specified prefix from a private B2 bucket.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_get_download_authorization.html
	 * 
	 * @param string                 $bucketId       The identifier for the bucket.
	 * @param string                 $fileNamePrefix The file name prefix of files the download authorization token will allow access.
	 * @param int                    $validDuration  The number of seconds before the authorization token will expire. The minimum
	 *                                               value is `1` second. The maximum value is `604800`. Default: `604800`.
	 * @param DownloadOptions|array  $options        Additional options to pass to the API.
	 */
	public function getDownloadAuthorization(
		string $bucketId,
		string $fileNamePrefix,
		?int $validDuration = DownloadAuthorization::VALID_DURATION_MAX,
		$options = null
	): DownloadAuthorization {
		if (!$options instanceof DownloadOptions) {
			$options = DownloadOptions::fromArray($options ?? []);
		}

		$response = $this->guzzle->request('POST', '/b2_get_download_authorization', [
			'json' => AbstractService::filterRequestOptions([
				File::ATTRIBUTE_BUCKET_ID        => $bucketId,
				File::ATTRIBUTE_FILE_NAME_PREFIX => $fileNamePrefix,
				File::ATTRIBUTE_VALID_DURATION   => $validDuration,
			], $options->getAuthorizationOptions()),
		]);

		return DownloadAuthorization::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Gets information about one file stored in B2.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_get_file_info.html
	 *
	 * @param string $fileId The ID of the file.
	 */
	public function getFileInfo(string $fileId): File
	{
		$response = $this->guzzle->request('POST', '/b2_get_file_info', [
			'json' => [
				File::ATTRIBUTE_FILE_ID => $fileId
			]
		]);

		return File::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Gets a URL to use for uploading parts of a large file. 
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_get_upload_part_url.html
	 * 
	 * @param string $fileId The ID of the large file to upload parts of.
	 */
	public function getUploadPartUrl(string $fileId): UploadPartUrl
	{
		$response = $this->guzzle->request('POST', '/b2_get_upload_part_url', [
			'json' => [
				File::ATTRIBUTE_FILE_ID => $fileId
			]
		]);

		return UploadPartUrl::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Gets a URL and authorization token to use for uploading files.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_get_upload_url.html
	 * 
	 * @param string $bucketId The ID of the bucket to upload files to.
	 * 
	 * @return UploadUrl
	 */
	public function getUploadUrl(string $bucketId): UploadUrl
	{
		$response = $this->guzzle->request('POST', '/b2_get_upload_url', [
			'json' => [
				File::ATTRIBUTE_BUCKET_ID => $bucketId
			]
		]);

		return UploadUrl::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Hides a file so that downloading by name will not find the file,
	 * but previous versions of the file are still stored.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_hide_file.html
	 * 
	 * @param string $bucketId
	 * @param string $fileName
	 */
	public function hideFile(string $bucketId, string $fileName): File
	{

		$response = $this->guzzle->request('POST', '/b2_hide_file', [
			'json' => [
				File::ATTRIBUTE_BUCKET_ID => $bucketId,
				File::ATTRIBUTE_FILE_NAME => $fileName,
			]
		]);

		return File::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Lists the names of all files in a bucket, starting at a given name.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_list_file_names.html
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
		string $bucketId,
		?string $prefix = null,
		?string $delimiter = null,
		?string $startFileName = null,
		?int $maxFileCount = 1000
	): FileList {
		$response = $this->guzzle->request('POST', '/b2_list_file_names', [
			'json' => AbstractService::filterRequestOptions([
				File::ATTRIBUTE_BUCKET_ID      => $bucketId,
				File::ATTRIBUTE_MAX_FILE_COUNT => $maxFileCount,
			], [
				File::ATTRIBUTE_PREFIX => $prefix,
				File::ATTRIBUTE_DELIMITER => $delimiter,
				File::ATTRIBUTE_START_FILE_NAME => $startFileName,
			]),
		]);

		return FileList::create($response);
	}

	/**
	 * Lists all of the versions of all of the files contained in one bucket, in alphabetical order by file name, and
	 * by reverse of date/time uploaded for versions of files with the same name. 
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_list_file_versions.html
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
		string $bucketId,
		?string $prefix = '',
		?string $delimiter = null,
		?string $startFileName = null,
		?string $startFileId = null,
		?int $maxFileCount = 1000
	): FileList {
		$response = $this->guzzle->request('POST', '/b2_list_file_versions', [
			'json' => AbstractService::filterRequestOptions([
				File::ATTRIBUTE_BUCKET_ID      => $bucketId,
				File::ATTRIBUTE_MAX_FILE_COUNT => $maxFileCount,
			], [
				File::ATTRIBUTE_START_FILE_NAME => $startFileName,
				File::ATTRIBUTE_START_FILE_ID   => $startFileId,
				File::ATTRIBUTE_PREFIX          => $prefix,
				File::ATTRIBUTE_DELIMITER       => $delimiter,
			]),
		]);

		return FileList::create($response);
	}

	/**
	 * Lists the parts that have been uploaded for a large file that has not been finished yet. 
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_list_parts.html
	 * 
	 * @param string $fileId          The ID returned by b2_start_large_file. This is the file whose parts will be
	 *                                listed.
	 * @param int    $startPartNumber The first part to return. Used when a query hits the maxKeyCount, and you want to
	 *                                get more.
	 * @param int    $maxPartCount    The maximum number of parts to return in the response. The default value is 1000,
	 *                                and the maximum is 10000. The maximum number of parts returned per transaction
	 *                                is 1000.
	 *                                If more than 1000 are returned, the call will be billed as multiple transactions.
	 * @param bool   $loop            Make API requests until there are no keys left.
	 */
	public function listParts(
		string $fileId,
		?int $startPartNumber = null,
		?int $maxPartCount = 1000
	): FilePartList {
		$response = $this->guzzle->request('POST', '/b2_list_parts', [
			'json' => AbstractService::filterRequestOptions([
				File::ATTRIBUTE_FILE_ID => $fileId
			], [
				File::ATTRIBUTE_START_PART_NUMBER => $startPartNumber,
				File::ATTRIBUTE_MAX_PART_COUNT    => $maxPartCount,
			]),
		]);

		return FilePartList::create($response);
	}

	/**
	 * Lists information about large file uploads that have been started,
	 * but that have not been finished or canceled.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_list_unfinished_large_files.html
	 * 
	 * @param string $bucketId     The bucket to look for file names in.
	 * @param string $namePrefix   Only return files whose names match this prefix.
	 * @param string $startFileId  The first upload to return.
	 * @param int    $maxFileCount The maximum number of files to return from this call. The default value is 100, and
	 *                             the maximum allowed is 100.
	 */
	public function listUnfinishedLargeFiles(
		string $bucketId,
		?string $namePrefix = null,
		?string $startFileId = null,
		?int $maxFileCount = 100
	): FileList {
		$response = $this->guzzle->request('POST', '/b2_list_unfinished_large_files', [
			'json' => AbstractService::filterRequestOptions([
				File::ATTRIBUTE_BUCKET_ID      => $bucketId,
				File::ATTRIBUTE_MAX_FILE_COUNT => $maxFileCount,
			], [
				File::ATTRIBUTE_NAME_PREFIX   => $namePrefix,
				File::ATTRIBUTE_START_FILE_ID => $startFileId,
			]),
		]);

		return FileList::create($response);
	}

	/**
	 * Prepares for uploading the parts of a large file. 
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_start_large_file.html
	 * 
	 * @param string         $bucketId    The ID of the bucket that the file will go in. 
	 * @param string         $fileName    The name of the file.
	 * @param string         $contentType The MIME type of the content of the file.
	 * @param FileInfo|array $fileInfo    A JSON object holding the name/value pairs for the custom file info.
	 */
	public function startLargeFile(
		string $bucketId,
		string $fileName,
		?string $contentType = null,
		$fileInfo = null,
		?array $fileRetention = null,
		?array $legalHold = null,
		?array $serverSideEncryption = null
	): File {
		if (!$fileInfo instanceof FileInfo) {
			$fileInfo = FileInfo::fromArray($fileInfo);
		}

		$response = $this->guzzle->request('POST', '/b2_start_large_file', [
			'json' => AbstractService::filterRequestOptions([
				File::ATTRIBUTE_BUCKET_ID    => $bucketId,
				File::ATTRIBUTE_FILE_NAME    => $fileName,
				File::ATTRIBUTE_CONTENT_TYPE => $contentType ?? File::CONTENT_TYPE_AUTO,
			], [
				File::ATTRIBUTE_FILE_INFO => $fileInfo->get(),
				File::ATTRIBUTE_FILE_RETENTION => $fileRetention,
				File::ATTRIBUTE_LEGAL_HOLD => $legalHold,
				File::ATTRIBUTE_SSE => $serverSideEncryption,
			])
		]);

		return File::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Update the File Lock legal hold status for an existing file.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_update_file_legal_hold.html
	 * 
	 * @param string $fileName  The name of the file.
	 * @param string $fileId    The ID of the file.
	 * @param string $legalHold The legal hold status of the file. `on` = *enabled*; `off` = *disabled*.
	 */
	public function updateFileLegalHold(
		string $fileName,
		string $fileId,
		string $legalHold
	): File {
		$response = $this->guzzle->request('POST', '/b2_update_file_legal_hold', [
			'json' => AbstractService::filterRequestOptions([
				File::ATTRIBUTE_FILE_NAME  => $fileName,
				File::ATTRIBUTE_FILE_ID    => $fileId,
				File::ATTRIBUTE_LEGAL_HOLD => $legalHold,
			]),
		]);

		return File::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 *  Update the File Lock retention settings for an existing file.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_update_file_retention.html
	 * 
	 * @param string $fileName         The name of the file.
	 * @param string $fileId           The ID of the file.
	 * @param string $fileRetention    The legal hold status of the file. `on` = *enabled*; `off` = *disabled*.
	 * @param bool   $bypassGovernance To shorten or remove an existing governance mode retention setting,
	 *                                 this must also be specified and set to `true`.
	 */
	public function updateFileRetention(
		string $fileName,
		string $fileId,
		string $fileRetention,
		?bool $bypassGovernance = false
	): File {
		$response = $this->guzzle->request('POST', '/b2_update_file_retention', [
			'json' => AbstractService::filterRequestOptions([
				File::ATTRIBUTE_FILE_NAME         => $fileName,
				File::ATTRIBUTE_FILE_ID           => $fileId,
				File::ATTRIBUTE_FILE_RETENTION    => $fileRetention,
				File::ATTRIBUTE_BYPASS_GOVERNANCE => $bypassGovernance,
			]),
		]);

		return File::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Uploads a file to a bucket and returns the uploaded file as an object.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_upload_file.html
	 *
	 * @param string                     $bucketId             The ID of the bucket to upload the file to.
	 * @param string                     $fileName             The name of the file.
	 * @param string|resource            $body                 The file to be uploaded. String or stream resource.
	 * @param string                     $contentType          The MIME type of the content of the file.
	 * @param FileInfo|array             $fileInfo             The custom file info to add to the uploaded file.
	 * @param ServerSideEncryption|array $serverSideEncryption The parameters for B2 to encrypt the uploaded file.
	 * @param UploadUrl                  $uploadUrl            The upload authorization data.
	 */
	public function uploadFile(
		string $bucketId,
		string $fileName,
		$body,
		?string $contentType = null,
		$fileInfo = null,
		$serverSideEncryption = null,
		?UploadUrl $uploadUrl = null
	): File {
		if (!$fileInfo instanceof FileInfo) {
			$fileInfo = FileInfo::fromArray($fileInfo ?? []);
		}

		if (!$serverSideEncryption instanceof ServerSideEncryption) {
			$serverSideEncryption = ServerSideEncryption::fromArray($serverSideEncryption ?? []);
		}

		if (!$uploadUrl instanceof UploadUrl) {
			$uploadUrl = $this->getUploadUrl($bucketId);
		}

		$uploadMetadata = FileUploadMetadata::fromResource($body);
		$mtime = $uploadMetadata->getLastModifiedTimestamp();
		if ($mtime > 0){
			$fileInfo->setLastModifiedTimestamp($mtime);
		}

		$response = $this->guzzle->request('POST', $uploadUrl->getUploadUrl(), [
			'body'    => $body,
			'headers' => AbstractService::filterRequestOptions([
				'Authorization'                => $uploadUrl->getAuthorizationToken(),
				'Content-Type'                 => $contentType ?? File::CONTENT_TYPE_AUTO,
				'Content-Length'               => $uploadMetadata->getLength(),
				File::HEADER_X_BZ_CONTENT_SHA1 => $uploadMetadata->getSha1(),
				File::HEADER_X_BZ_FILE_NAME    => urlencode($fileName),
			], 
				($serverSideEncryption->getHeaders() ?? []) +
				($fileInfo->getHeaders() ?? [])
			),
		]);

		return File::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Uploads one part of a large file.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_upload_part.html
	 * 
	 * @param string|resource            $body                 The file part to be uploaded. String or stream resource.
	 * @param string                     $fileId               The ID of the large file whose parts you want to upload.
	 * @param int                        $partNumber           The parts uploaded for one file must have contiguous numbers, starting with 1.
	 * @param ServerSideEncryption|array $serverSideEncryption The parameters for B2 to encrypt the uploaded file.
	 * @param UploadPartUrl              $uploadPartUrl        The upload part authorization data.
	 */
	public function uploadPart(
		$body,
		string $fileId,
		?int $partNumber = 1,
		$serverSideEncryption = null,
		?UploadPartUrl $uploadPartUrl = null,
		?FileUploadMetadata $metadata = null
	): File {
		if (!$uploadPartUrl instanceof UploadPartUrl) {
			$uploadPartUrl = $this->getUploadPartUrl($fileId);
		}

		if (!$metadata instanceof FileUploadMetadata) {
			$metadata = FileUploadMetadata::fromResource($body);
		}

		$response = $this->guzzle->request('POST', $uploadPartUrl->getUploadUrl(), [
			'body' => $body,
			'headers' => self::filterRequestOptions([
				'Authorization'                => $uploadPartUrl->getAuthorizationToken(),
				'Content-Length'               => $metadata->getLength(),
				File::HEADER_X_BZ_CONTENT_SHA1 => $metadata->getSha1(),
				File::HEADER_X_BZ_PART_NUMBER  => $partNumber,
			], $serverSideEncryption->getHeaders() ?? []),
		]);

		return File::fromArray(json_decode((string) $response->getBody(), true));
	}
}
