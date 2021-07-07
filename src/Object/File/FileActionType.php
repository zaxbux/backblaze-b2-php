<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Object\File;

use InvalidArgumentException;

/** @package Zaxbux\BackblazeB2\Object\File */
final class FileActionType
{
	/**
	 * A file that was uploaded to B2 Cloud Storage.
	 */
	public const UPLOAD = 'upload';

	/**
	 * A large file has been started, but not finished or canceled.
	 */
	public const START  = 'start';

	/**
	 * A file that was uploaded to B2 Cloud Storage.
	 */
	public const COPY = 'copy';
	
	/**
	 * A file version marking the file as hidden.
	 */
	public const HIDE   = 'hide';
	
	/**
	 * A virtual folder.
	 */
	public const FOLDER = 'folder';

	private $action;

	/**
	 * @param string $action 
	 */
	public function __construct(string $action)
	{
		$this->action = $action;
	}

	/**
	 * @inheritdoc
	 */
	public function __toString()
	{
		return $this->action;
	}

	/**
	 * @param string $action 
	 * @return FileActionType 
	 * @throws InvalidArgumentException 
	 */
	public static function fromString(string $action) {
		//$actions = [static::UPLOAD, static::START, static::COPY, static::HIDE, static::FOLDER];

		/*if (!\in_array($action, $actions)) {
			throw new InvalidArgumentException('Argument $action must be one of: ' . implode(', ', $actions));
		}*/

		return new FileActionType($action);
	}

	/**
	 * Check if this file object is a finished upload.
	 */
	public function isUpload(): bool
	{
		return $this->action === FileActionType::UPLOAD;
	}

	/**
	 * Check if this file object is an unfinished large file.
	 */
	public function isUnfinishedLargeFile(): bool
	{
		return $this->action === FileActionType::START;
	}

	/**
	 * Check if this file object is a copied upload.
	 */
	public function isCopy(): bool
	{
		return $this->action === FileActionType::COPY;
	}

	/**
	 * Check if this file object is a previous file version, thus hidden.
	 */
	public function isHidden(): bool
	{
		return $this->action === FileActionType::HIDE;
	}

	/**
	 * Check if this file object is a virtual folder.
	 */
	public function isFolder(): bool
	{
		return $this->action === FileActionType::FOLDER;
	}
}
