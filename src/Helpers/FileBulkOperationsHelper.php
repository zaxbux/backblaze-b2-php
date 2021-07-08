<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Helpers;

use Zaxbux\BackblazeB2\Client;
use Zaxbux\BackblazeB2\Response\FileList;
use Zaxbux\BackblazeB2\Object\File;

/** @package BackblazeB2\Helpers */
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

	public function list(): FileList
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
			$this->client->updateFileLegalHold($version->id(), $version->name(), $legalHold);
		});
	}

	public function updateRetention(array $fileRetention, ?bool $bypassGovernance = false): FileList
	{
		return $this->apply(function($version) use ($fileRetention, $bypassGovernance) {
			$this->client->updateFileRetention(
				$version->id(),
				$version->name(),
				$fileRetention,
				$bypassGovernance
			);
		});
	}

	/**
	 * Applies a callback to each file version.
	 * 
	 * @param callable $operation `$operation(File $version): FileList`
	 */
	public function apply(callable $operation): FileList
	{
		$fileVersions = $this->list();

		$changed = new FileList();

		while ($fileVersions->valid()) {
			$version = $fileVersions->current();

			$changed->append($operation($version));

			$fileVersions->next();
		}

		return $changed;
	}
}