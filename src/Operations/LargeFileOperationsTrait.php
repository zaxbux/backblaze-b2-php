<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Operations;

use Zaxbux\BackblazeB2\Http\Endpoint;
use Zaxbux\BackblazeB2\Object\AccountAuthorization;
use Zaxbux\BackblazeB2\Object\File;
use Zaxbux\BackblazeB2\Object\File\FileUploadMetadata;
use Zaxbux\BackblazeB2\Object\File\FileInfo;
use Zaxbux\BackblazeB2\Object\File\ServerSideEncryption;
use Zaxbux\BackblazeB2\Object\File\UploadPartUrl;
use Zaxbux\BackblazeB2\Response\FileList;
use Zaxbux\BackblazeB2\Response\FilePartList;
use Zaxbux\BackblazeB2\Utils;

/** @package BackblazeB2\Operations */
trait LargeFileOperationsTrait
{

	/** @var \GuzzleHttp\ClientInterface */
	protected $http;

	abstract protected function accountAuthorization(): AccountAuthorization;

	/**
	 * Cancel the upload of a large file, and deletes all of the parts that have been uploaded.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_cancel_large_file.html
	 * 
	 * @b2-capability writeFiles
	 * @b2-transaction Class A
	 * 
	 * @param string $fileId The ID returned by `b2_start_large_file`.
	 */
	public function cancelLargeFile(string $fileId)
	{
		$response = $this->http->request('POST', Endpoint::CANCEL_LARGE_FILE, [
			'json' => [
				File::ATTRIBUTE_FILE_ID => $fileId,
			],
		]);

		return File::fromResponse($response);
	}

	/**
	 * Copies from an existing B2 file, storing it as a part of a large file which has already been started with
	 * `b2_start_large_file`.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_copy_part.html
	 * 
	 * @b2-capability writeFiles
	 * @b2-capability [readFiles] If the bucket is private.
	 * @b2-transaction Class C
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
		$response = $this->http->request('POST', Endpoint::COPY_PART, [
			'json' => Utils::filterRequestOptions([
				File::ATTRIBUTE_SOURCE_FILE_ID => $sourceFileId,
				File::ATTRIBUTE_LARGE_FILE_ID  => $largeFileId,
				File::ATTRIBUTE_PART_NUMBER    => $partNumber,
			], [
				File::ATTRIBUTE_RANGE           => $range,
				File::ATTRIBUTE_SOURCE_SSE      => $sourceSSE,
				File::ATTRIBUTE_DESTINATION_SSE => $destinationSSE,
			]),
		]);

		return File::fromResponse($response);
	}

	/**
	 * Converts the parts that have been uploaded into a single B2 file.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_finish_large_file.html
	 * 
	 * @b2-capability writeFiles
	 * @b2-transaction Class A
	 * 
	 * @param string   $fileId The ID of the large file.
	 * @param string[] $hashes An array of SHA1 checksums of the parts of the large file.
	 * 
	 * @return File
	 */
	public function finishLargeFile(string $fileId, array $hashes)
	{
		$response = $this->http->request('POST', Endpoint::FINISH_LARGE_FILE, [
			'json' => [
				File::ATTRIBUTE_FILE_ID         => $fileId,
				File::ATTRIBUTE_PART_SHA1_ARRAY => $hashes,
			]
		]);

		return File::fromResponse($response);
	}

	/**
	 * Gets a URL to use for uploading parts of a large file. 
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_get_upload_part_url.html
	 * 
	 * @b2-capability writeFiles
	 * @b2-transaction Class A
	 * 
	 * @param string $fileId The ID of the large file to upload parts of.
	 */
	public function getUploadPartUrl(string $fileId): UploadPartUrl
	{
		$response = $this->http->request('POST', Endpoint::GET_UPLOAD_PART_URL, [
			'json' => [
				File::ATTRIBUTE_FILE_ID => $fileId
			]
		]);

		return UploadPartUrl::fromResponse($response);
	}

	/**
	 * Lists the parts that have been uploaded for a large file that has not been finished yet. 
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_list_parts.html
	 * 
	 * @b2-capability writeFiles
	 * @b2-transaction Class C
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
		$response = $this->http->request('POST', Endpoint::LIST_PARTS, [
			'json' => Utils::filterRequestOptions([
				File::ATTRIBUTE_FILE_ID => $fileId
			], [
				File::ATTRIBUTE_START_PART_NUMBER => $startPartNumber,
				File::ATTRIBUTE_MAX_PART_COUNT    => $maxPartCount,
			]),
		]);

		return FilePartList::fromResponse($response);
	}

	/**
	 * Lists information about large file uploads that have been started,
	 * but that have not been finished or canceled.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_list_unfinished_large_files.html
	 * 
	 * @b2-capability listFiles
	 * @b2-transaction Class C
	 * 
	 * @param string $bucketId     The bucket to look for file names in.
	 * @param string $namePrefix   Only return files whose names match this prefix.
	 * @param string $startFileId  The first upload to return.
	 * @param int    $maxFileCount The maximum number of files to return from this call. The default value is 100, and
	 *                             the maximum allowed is 100.
	 */
	public function listUnfinishedLargeFiles(
		?string $bucketId = null,
		?string $namePrefix = null,
		?string $startFileId = null,
		?int $maxFileCount = 100
	): FileList {
		$response = $this->http->request('POST', Endpoint::LIST_UNFINISHED_LARGE_FILES, [
			'json' => Utils::filterRequestOptions([
				File::ATTRIBUTE_BUCKET_ID      => $bucketId ?? $this->getAllowedBucketId(),
			], [
				File::ATTRIBUTE_NAME_PREFIX   => $namePrefix,
				File::ATTRIBUTE_START_FILE_ID => $startFileId,
				File::ATTRIBUTE_MAX_FILE_COUNT => $maxFileCount,
			]),
		]);

		return FileList::fromResponse($response);
	}

	/**
	 * Prepares for uploading the parts of a large file. 
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_start_large_file.html
	 * 
	 * @b2-capability writeFiles
	 * @b2-transaction Class A
	 * 
	 * @param string         $bucketId    The ID of the bucket that the file will go in. 
	 * @param string         $fileName    The name of the file.
	 * @param string         $contentType The MIME type of the content of the file.
	 * @param FileInfo|array $fileInfo    A JSON object holding the name/value pairs for the custom file info.
	 */
	public function startLargeFile(
		string $fileName,
		?string $bucketId = null,
		?string $contentType = null,
		$fileInfo = null,
		?array $fileRetention = null,
		?array $legalHold = null,
		?array $serverSideEncryption = null
	): File {
		if (!$fileInfo instanceof FileInfo) {
			$fileInfo = FileInfo::fromArray($fileInfo);
		}

		$response = $this->http->request('POST', Endpoint::START_LARGE_FILE, [
			'json' => Utils::filterRequestOptions([
				File::ATTRIBUTE_BUCKET_ID    => $bucketId ?? $this->getAllowedBucketId(),
				File::ATTRIBUTE_FILE_NAME    => $fileName,
				File::ATTRIBUTE_CONTENT_TYPE => $contentType ?? File::CONTENT_TYPE_AUTO,
			], [
				File::ATTRIBUTE_FILE_INFO => $fileInfo->get(),
				File::ATTRIBUTE_FILE_RETENTION => $fileRetention,
				File::ATTRIBUTE_LEGAL_HOLD => $legalHold,
				File::ATTRIBUTE_SSE => $serverSideEncryption,
			])
		]);

		return File::fromResponse($response);
	}

	/**
	 * Uploads one part of a large file.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_upload_part.html
	 * 
	 * @b2-capability writeFiles
	 * @b2-transaction Class A
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

		$response = $this->http->request('POST', $uploadPartUrl->getUploadUrl(), [
			'body' => $body,
			'headers' => self::filterRequestOptions([
				'Authorization'                => $uploadPartUrl->getAuthorizationToken(),
				'Content-Length'               => $metadata->getLength(),
				File::HEADER_X_BZ_CONTENT_SHA1 => $metadata->getSha1(),
				File::HEADER_X_BZ_PART_NUMBER  => $partNumber,
			], $serverSideEncryption->getHeaders() ?? []),
		]);

		return File::fromResponse($response);
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
		$allParts = new FileList();
		$nextPartNumber = $startPartNumber ?? 0;

		while ($nextPartNumber !== null) {
			$response       = $this->listParts($fileId, $startPartNumber);
			$nextPartNumber = $response->getNextPartNumber();

			$allParts->mergeList($response);
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
		?string $bucketId = null,
		?string $namePrefix = null,
		?string $startFileId = null,
		?int $maxFileCount = 100
	): iterable {

		$allFiles = new FileList();
		$nextFileId = $startFileId ?? '';

		while ($nextFileId !== null) {
			$response   = $this->listUnfinishedLargeFiles($bucketId, $namePrefix, $startFileId, $maxFileCount);
			$nextFileId = $response->getNextFileId();

			$allFiles->mergeList($response);
		}

		return $allFiles;
	}

}
