<?php

namespace Zaxbux\BackblazeB2\Response;

use Psr\Http\Message\ResponseInterface;
use Zaxbux\BackblazeB2\Object\File;
use Zaxbux\BackblazeB2\Utils;

/** @package BackblazeB2\Response */
class FilePartList extends AbstractListResponse
{
	public const ATTRIBUTE_PARTS                 = 'parts';
	public const ATTRIBUTE_NEXT_PART_NUMBER      = 'nextPartNumber';
	
	/** @var string */
	private $nextPartNumber;

	public function __construct(
		?array $parts = [],
		?string $nextPartNumber = null
	) {
			parent::__construct($parts);
			$this->nextPartNumber = $nextPartNumber;
	}

	public function current(): File
	{
		$value = parent::current();
		return $value instanceof File ? $value : File::fromArray($value);
	}

	/**
	 * Get the value of nextPartNumber.
	 */ 
	public function getNextPartNumber(): ?string
	{
		return $this->nextPartNumber;
	}

	/**
	 * @inheritdoc
	 * 
	 * @return FilePartList
	 */
	public static function fromArray($data): FilePartList
	{
		return new static(
			$data[static::ATTRIBUTE_PARTS],
			$data[static::ATTRIBUTE_NEXT_PART_NUMBER]
		);
	}
}