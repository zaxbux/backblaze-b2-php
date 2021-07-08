<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Helpers;

use BadMethodCallException;
use Zaxbux\BackblazeB2\Object\File;

/** @package BackblazeB2\Helpers */
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

	private function assertFileIsSet(): void
	{
		if (!$this->file) {
			throw new BadMethodCallException('$file is not set');
		}
	}
}