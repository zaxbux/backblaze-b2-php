<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\B2\Object;

use RuntimeException;
use Zaxbux\BackblazeB2\Classes\ObjectInfoBase;

/**
 * @link https://www.backblaze.com/b2/docs/files.html#fileInfo
 * 
 * @package Zaxbux\BackblazeB2\B2\Object
 */
final class FileInfo extends ObjectInfoBase {
	public const B2_FILE_INFO_MTIME = 'src_last_modified_millis';

	public static function fromArray(array $data): FileInfo {
		return new FileInfo($data);
	}

	/**
	 * @param int $timestamp     The timestamp, in milliseconds since UNIX epoch.
	 * @param bool $milliseconds Set `false` to convert time from seconds to milliseconds.
	 */
	public function setLastModifiedTimestamp(int $timestamp, ?bool $milliseconds = true) {
		$this->set(static::B2_FILE_INFO_MTIME, $milliseconds ? $timestamp : $timestamp * 1000);
	}
}
