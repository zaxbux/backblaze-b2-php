<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Traits;

use Zaxbux\BackblazeB2\Helpers\FileBulkOperationsHelper;

/** @package Zaxbux\BackblazeB2\Traits */
trait ApplyToAllFileVersionsTrait
{
	/**
	 * Apply operations to all versions of a file.
	 * 
	 * @param string $fileId 
	 * @return FileList 
	 */
	public function allFileVersions(string $fileName): FileBulkOperationsHelper
	{
		return FileBulkOperationsHelper::create($this)->withFileName($fileName);
	}
}