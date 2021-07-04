<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Class;

use function pathinfo;

/** @package Zaxbux\BackblazeB2 */
final class FilePathInfo
{
	
	/**
	 * Directory path containing the file.
	 * 
	 * @var string
	 */
	public $dirname;
	
	/**
	 * File name, including extension.
	 * 
	 * @var string
	 */
	public $basename;
	
	/**
	 * File name, excluding extension.
	 * 
	 * @var string
	 */
	public $filename;

	/**
	 * File extension, if any.
	 * @var null|string
	 */
	public $extension;

	public function __construct(
		string $dirname,
		string $basename,
		string $filename,
		?string $extension = null
	) {
		$this->dirname = $dirname;
		$this->basename = $basename;
		$this->filename = $filename;
		$this->extension = $extension;
	}

	/**
	 * @param string $path The path to extract information from.
	 */
	public static function fromPath(string $path): FilePathInfo {
		return static::fromArray(pathinfo($path));
	}

	/**
	 * @param array $pathinfo
	 */
	public static function fromArray(array $pathinfo): FilePathInfo {
		return new FilePathInfo(
			$pathinfo['dirname'],
			$pathinfo['basename'],
			$pathinfo['filename'],
			$pathinfo['extension'] ?? null
		);
	}
}
