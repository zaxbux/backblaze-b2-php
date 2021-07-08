<?php

namespace Zaxbux\BackblazeB2\Response;

use Zaxbux\BackblazeB2\Object\File;

/** @package BackblazeB2\Response */
class FileList extends AbstractListResponse
{
	public const ATTRIBUTE_FILES                 = 'files';
	public const ATTRIBUTE_NEXT_FILE_ID          = 'nextFileId';
	public const ATTRIBUTE_NEXT_FILE_NAME        = 'nextFileName';
	
	
	/** @var string */
	private $nextFileId;
	
	/** @var string */
	private $nextFileName;

	public function __construct(
		?array $files = [],
		?string $nextFileId = null,
		?string $nextFileName = null
	) {
		parent::__construct($files);
		$this->nextFileId   = $nextFileId;
		$this->nextFileName = $nextFileName;
	}

	public function current(): File
	{
		$value = parent::current();
		return $value instanceof File ? $value : File::fromArray($value);
	}


	/**
	 * Get the value of nextFileId.
	 */ 
	public function nextFIleId(): ?string
	{
		return $this->nextFileId;
	}

	/**
	 * Get the value of nextFileName.
	 */ 
	public function nextFIleName(): ?string
	{
		return $this->nextFileName;
	}

	protected static function fromArray($data): FileList
	{
		return new static(
			$data[static::ATTRIBUTE_FILES],
			// Not set when listing files by name
			$data[static::ATTRIBUTE_NEXT_FILE_ID] ?? null,
			// Not set when listing large files
			$data[static::ATTRIBUTE_NEXT_FILE_NAME] ?? null
		);
	}
}