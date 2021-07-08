<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Helpers;

use BadMethodCallException;
use Zaxbux\BackblazeB2\Object\File;
use Zaxbux\BackblazeB2\Object\File\ServerSideEncryption;
use Zaxbux\BackblazeB2\Response\FileDownload;
use Zaxbux\BackblazeB2\Response\FileList;

/**
 * Helper class of convenience methods for file operations.
 * 
 * @package BackblazeB2\Helpers
 */
class FileOperationsHelper extends AbstractHelper {

	/** @var \Zaxbux\BackblazeB2\Object\File */
	private $file;

	/**
	 * Specify which file to preform operations on.
	 * @param null|file $file 
	 * @return FileOperationsHelper 
	 */
	public function withFile(?file $file): FileOperationsHelper
	{
		$this->file = $file;
		return $this;
	}

	public function copy(
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
		$this->assertFileIsSet();
		return $this->client->copyFile(
			$this->file->getId(),
			$fileName,
			$destinationBucketId,
			$range,
			$metadataDirective,
			$contentType,
			$fileInfo,
			$fileRetention,
			$legalHold,
			$sourceSSE,
			$destinationSSE
		);
	}

	public function deleteVersion(?bool $bypassGovernance = false): File
	{
		$this->assertFileIsSet();
		return $this->client->deleteFileVersion($this->file->getId(), $this->file->getName(), $bypassGovernance);
	}

	public function hide(): File
	{
		$this->assertFileIsSet();
		return $this->client->hideFile($this->file->getId());
	}

	public function updateLegalHold(string $legalHold): File
	{
		$this->assertFileIsSet();
		return $this->client->updateFileLegalHold($this->file->getId(), $this->file->getName(), $legalHold);
	}

	public function updateRetention(array $retention, ?bool $bypassGovernance = false): File
	{
		$this->assertFileIsSet();
		return $this->client->updateFileRetention(
			$this->file->getId(),
			$this->file->getName(),
			$retention,
			$bypassGovernance
		);
	}

	public function deleteAllVersions(?bool $bypassGovernance = false): FileList
	{
		$this->assertFileIsSet();
		return $this->client->deleteAllFileVersions(
			$this->file->getId(),
			$this->file->getName(),
			null,
			null,
			null,
			$bypassGovernance
		);
	}

	public function download(
		$options = null,
		$sink = null,
		?bool $headersOnly = false
	): FileDownload {
		$this->assertFileIsSet();

		return $this->client->downloadFileById(
			$this->file->getId(),
			$options,
			$sink,
			$headersOnly
		);
	}

	public function getInfo(string $fileId): File
	{
		return $this->client->getFileInfo($fileId);
	}

	public function getById(string $fileId): File
	{
		return $this->client->getFileById($fileId);
	}

	public function getByName(string $fileName): File
	{
		return $this->client->getFileByName($fileName);
	}

	private function assertFileIsSet(): void
	{
		if (!$this->file) {
			throw new BadMethodCallException('$file is not set');
		}
	}
}
