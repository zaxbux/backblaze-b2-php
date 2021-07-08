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
			$this->file->id(),
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
		return $this->client->deleteFileVersion($this->file->id(), $this->file->name(), $bypassGovernance);
	}

	public function hide(): File
	{
		$this->assertFileIsSet();
		return $this->client->hideFile($this->file->id());
	}

	public function updateLegalHold(string $legalHold): File
	{
		$this->assertFileIsSet();
		return $this->client->updateFileLegalHold($this->file->id(), $this->file->name(), $legalHold);
	}

	public function updateRetention(array $retention, ?bool $bypassGovernance = false): File
	{
		$this->assertFileIsSet();
		return $this->client->updateFileRetention(
			$this->file->id(),
			$this->file->name(),
			$retention,
			$bypassGovernance
		);
	}

	public function deleteAllVersions(?bool $bypassGovernance = false): FileList
	{
		$this->assertFileIsSet();
		return $this->client->deleteAllFileVersions(
			$this->file->id(),
			$this->file->name(),
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
			$this->file->id(),
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
