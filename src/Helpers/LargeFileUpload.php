<?php

namespace Zaxbux\BackblazeB2\Helpers;

use RuntimeException;
use Zaxbux\BackblazeB2\Client;
use Zaxbux\BackblazeB2\Object\File;
use Zaxbux\BackblazeB2\Object\File\FileInfo;
use Zaxbux\BackblazeB2\Object\File\FileUploadMetadata;
use Zaxbux\BackblazeB2\Object\File\ServerSideEncryption;

/** @package BackblazeB2\Helpers */
class LargeFileUpload {
	private $client;
	private $stream;
	private $fileName;

	private $contentLength;
	private $contentType;
	private $contentSha1;

	private $fileRetention;
	private $legalHoldStatus;
	private $serverSideEncryption;

	private $file;

	private $uploadPartUrl;

	private $bytesSent = 0;
	private $partCount = 0;

	/** @var array */
	private $partSha1Array = [];

	public function __construct(Client $client)
	{
		$this->client = $client;
	}

	public static function create(Client $client) {
		return new static($client);
	}

	public function withStream(
		$stream,
		string $fileName,
	): LargeFileUpload {
		$this->stream = $stream;
		$this->fileName = $fileName;

		return $this;
	}

	public function useContentType(string $contentType): LargeFileUpload
	{
		$this->contentType = $contentType;
		return $this;
	}

	public function useFileMetadata(FileUploadMetadata $metadata): LargeFileUpload
	{
		$this->contentLength = $metadata->getLength();
		$this->contentSha1 = $metadata->getSha1();
		return $this;
	}

	public function useFileInfo(FileInfo $fileInfo): LargeFileUpload
	{
		$this->fileInfo = $fileInfo;
		return $this;
	}

	public function legalHold(?bool $enabled = true): LargeFileUpload
	{
		$this->legalHold = $enabled ? 'on' : 'off';

		return $this;
	}

	public function withFileRetention(array $fileRetention): LargeFileUpload
	{
		$this->fileRetention = $fileRetention;

		return $this;
	}

	public function withEncryption(ServerSideEncryption $sse = null): LargeFileUpload
	{
		$this->serverSideEncryption = $sse ?? new ServerSideEncryption();

		return $this;
	}

	public function start(string $bucketId): LargeFileUpload
	{
		if (!$this->stream) {
			throw new RuntimeException('Missing file pointer or stream.');
		}

		if (!$this->fileName) {
			throw new RuntimeException('File name not set.');
		}

		if (!$this->metadata) {
			$this->metadata = FileUploadMetadata::fromResource($this->stream);
		}

		if ($this->metadata->getLength() < $this->minimumPartSize()) {
			throw new RuntimeException('Upload size is less than absolute minimum part size.');
		}

		if ($this->metadata->getLength() > File::LARGE_FILE_MAX_SIZE) {
			throw new RuntimeException('Upload size exceeds large file limit.');
		}

		// Start large file
		$this->file = $this->client->startLargeFile(
			$bucketId,
			$this->fileName,
			$this->contentType,
			$this->fileInfo,
			$this->fileRetention,
			$this->legalHoldStatus,
			$this->serverSideEncryption
		);

		// Get upload part url
		$this->uploadPartUrl = $this->client->getUploadPartUrl($this->file->getId());

		return $this;
	}

	public function uploadParts() {
		while ($this->remainingBytes() > 0) {
			$this->partCount++;
			$offset = $this->bytesToSend();

			$partString = fread($this->stream, $offset);
			
			$metadata = FileUploadMetadata::fromResource($partString);
			array_push($this->partSha1Array, $metadata->getSha1());

			$part = $this->client->uploadPart(
				$partString,
				$this->file->getId(),
				$this->partCount,
				$this->serverSideEncryption,
				$this->uploadPartUrl,
				$metadata
			);

			$this->bytesSent += $offset;
		}

		return $this;
	}

	public function finish() {
		// Finish large file

		// Get file info to confirm
		return $this;
	}

	public function getFile() {
		return $this->file;
	}

	private function minimumPartSize(): int
	{
		return $this->client->accountAuthorization()->getAbsoluteMinimumPartSize();
	}

	private function recommendedPartSize(): int
	{
		return $this->client->accountAuthorization()->getRecommendedPartSize();
	}

	private function remainingBytes(): int
	{
		return $this->contentLength - $this->bytesSent;
	}

	private function bytesToSend(): int
	{
		// Return the remaining byte size if less than the minimum part size (should be the last part of the file to upload)
		if ($this->remainingBytes() < $this->minimumPartSize()) {
			return $this->remainingBytes();
		}
		
		return $this->recommendedPartSize();
	}
}