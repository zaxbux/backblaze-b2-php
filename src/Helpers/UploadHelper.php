<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Helpers;

use RuntimeException;
use Zaxbux\BackblazeB2\Object\File;
use Zaxbux\BackblazeB2\Object\File\FileInfo;
use Zaxbux\BackblazeB2\Object\File\FileUploadMetadata;
use Zaxbux\BackblazeB2\Object\File\ServerSideEncryption;
use Zaxbux\BackblazeB2\Object\File\UploadUrl;

/** @package BackblazeB2\Helpers */
class UploadHelper extends AbstractHelper {
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
		?array $fileRetention = null,
		?bool $legalHold = null,
		?ServerSideEncryption $serverSideEncryption = null
	): File {
		$bucketId = $bucketIdOrUploadUrl;

		if ($bucketIdOrUploadUrl instanceof UploadUrl) {
			// Get bucket ID from Upload URL object.
			$bucketId = $bucketIdOrUploadUrl->bucketId();
		} else {
			// Set null to force fetching an Upload URL.
			$bucketIdOrUploadUrl = null;
		}

		$metadata = FileUploadMetadata::fromResource($stream);

		if ($metadata->length() < File::SINGLE_FILE_MIN_SIZE || $metadata->length() > File::LARGE_FILE_MAX_SIZE) {
			throw new RuntimeException(sprintf(
				'Upload size is not between %d bytes and %d bytes.',
				File::SINGLE_FILE_MIN_SIZE,
				File::LARGE_FILE_MAX_SIZE
			));
		}

		// Upload as large file if greater than single file size or configured size,
		// and greater than the minimum part size for the account.
		if (($metadata->length() > File::SINGLE_FILE_MAX_SIZE ||
			$metadata->length() > 200 * 1024 * 1024) &&
			$metadata->length() > $this->accountAuthorization->getAbsoluteMinimumPartSize()
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
		return $this->client->uploadFile(
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
	public function uploadFile(
		$bucketIdOrUploadUrl,
		string $fileName,
		string $filePath,
		?string $contentType = null,
		?FileInfo $fileInfo = null,
		?array $fileRetention =  null,
		?bool $legalHold = null,
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

		/* Seems to cause an issue with tests: https://github.com/guzzle/guzzle/issues/366#issuecomment-20295409
		fclose($handle);
		*/

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
		?array $fileRetention = null,
		?bool $legalHold = null,
		?ServerSideEncryption $serverSideEncryption = null,
		?FileUploadMetadata $metadata = null
	): File {
		$largeFileUpload = LargeFileUpload::create($this->client)->withStream($stream, $fileName);

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
}