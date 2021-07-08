<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Response;

use Zaxbux\BackblazeB2\Object\Key;

/** @package BackblazeB2\Response */
class KeyList extends AbstractListResponse {

	public const ATTRIBUTE_KEYS                    = 'keys';
	public const ATTRIBUTE_NEXT_APPLICATION_KEY_ID = 'nextApplicationKeyId';
	
	/** @var string */
	private $nextApplicationKeyId;

	public function __construct(?array $keys = [], ?string $nextApplicationKeyId = null) {
		parent::__construct($keys);
		$this->nextApplicationKeyId = $nextApplicationKeyId;
	}

	public function current(): Key
	{
		$value = parent::current();
		return $value instanceof Key ? $value : Key::fromArray($value);
	}

	public function nextApplicationKeyId(): ?string
	{
		return $this->nextApplicationKeyId;
	}

	protected static function fromArray($data): KeyList
	{
		return new static(
			$data[static::ATTRIBUTE_KEYS],
			$data[static::ATTRIBUTE_NEXT_APPLICATION_KEY_ID]
		);
	}
}