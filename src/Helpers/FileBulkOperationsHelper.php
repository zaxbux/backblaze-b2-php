<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Helpers;

use ArrayIterator;
use Iterator;
use Zaxbux\BackblazeB2\Client;
use Zaxbux\BackblazeB2\Response\FileList;
use Zaxbux\BackblazeB2\Object\File;

/** @package Zaxbux\BackblazeB2\Helpers */
class FileBulkOperationsHelper extends AbstractHelper
{
	private $fileName;

	public static function create(Client $client): FileBulkOperationsHelper
	{
		return new static($client);
	}

	public function withFileName(string $fileName): FileBulkOperationsHelper
	{
		$this->fileName = $fileName;
		return $this;
	}

	public function list(): Iterator
	{
		return $this->client->listAllFileVersions(null, null, null, $this->fileName);
	}

	public function delete(?bool $bypassGovernance = false): FileList
	{
		return $this->client->deleteAllFileVersions(null, $this->fileName, null, null, null, $bypassGovernance);
	}

	public function updateLegalHold(string $legalHold): FileList
	{
		return $this->apply(function(File $version) use ($legalHold) {
			$this->client->updateFileLegalHold($version->getId(), $version->getName(), $legalHold);
		});
	}

	public function updateRetention(array $fileRetention, ?bool $bypassGovernance = false): FileList
	{
		return $this->apply(function($version) use ($fileRetention, $bypassGovernance) {
			$this->client->updateFileRetention(
				$version->getId(),
				$version->getName(),
				$fileRetention,
				$bypassGovernance
			);
		});
	}

	/**
	 * Applies a callback to each file version.
	 * 
	 * @param callable(File):FileList $operation
	 */
	public function apply(callable $operation): FileList
	{
		$fileVersions = $this->list();

		$array = [];

		while ($fileVersions->valid()) {
			$version = $fileVersions->current();

			$array[] = $operation($version);

			$fileVersions->next();
		}

		return new FileList(new ArrayIterator($array));
	}
}