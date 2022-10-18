<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Operations;

use Zaxbux\BackblazeB2\Http\Endpoint;
use Zaxbux\BackblazeB2\Object\File;
use Zaxbux\BackblazeB2\Object\File\FileInfo;
use Zaxbux\BackblazeB2\Object\File\FileUploadMetadata;
use Zaxbux\BackblazeB2\Object\File\ServerSideEncryption;
use Zaxbux\BackblazeB2\Object\File\UploadUrl;
use Zaxbux\BackblazeB2\Utils;

/** @package BackblazeB2\Operations */
trait UploadOperationsTrait
{
	/** @var \Zaxbux\BackblazeB2\Config */
	protected $config;

	/**
	 * Gets a URL and authorization token to use for uploading files.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_get_upload_url.html
	 * 
	 * @b2-capability writeFiles
	 * @b2-transaction Class A
	 * 
	 * @param string $bucketId The ID of the bucket to upload files to.
	 * 
	 * @return UploadUrl
	 */
	public function getUploadUrl(?string $bucketId): UploadUrl
	{
		$response = $this->http->request('POST', Endpoint::GET_UPLOAD_URL, [
			'json' => [
				File::ATTRIBUTE_BUCKET_ID => $bucketId ?? $this->allowedBucketId()
			]
		]);

		return UploadUrl::fromResponse($response);
	}

	/**
	 * Uploads a file to a bucket and returns the uploaded file as an object.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_upload_file.html
	 * 
	 * @b2-capability writeFiles
	 * @b2-transaction Class A
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
		string $fileName,
		string $bucketId = null,
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
		$mtime = $uploadMetadata->lastModifiedTimestamp();
		if ($mtime > 0) {
			$fileInfo->setLastModifiedTimestamp($mtime);
		}

		$response = $this->http->request('POST', $uploadUrl->uploadUrl(), [
			'body'    => $body,
			'headers' => Utils::filterRequestOptions(
				[
					'Authorization'                => $uploadUrl->authorizationToken(),
					'Content-Type'                 => $contentType ?? File::CONTENT_TYPE_AUTO,
					'Content-Length'               => $uploadMetadata->length(),
					File::HEADER_X_BZ_CONTENT_SHA1 => $uploadMetadata->sha1(),
					File::HEADER_X_BZ_FILE_NAME    => urlencode($fileName),
				],
				($serverSideEncryption->getHeaders() ?? []),
				($fileInfo->getHeaders() ?? [])
			),
		]);

		return File::fromResponse($response);
	}
}
