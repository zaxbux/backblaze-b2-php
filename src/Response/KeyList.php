<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Response;

use Generator;
use GuzzleHttp\Utils;
use Psr\Http\Message\ResponseInterface;
use Zaxbux\BackblazeB2\Object\Key;


/** @package Zaxbux\BackblazeB2\Response */
class KeyList extends AbstractListResponse {
	
	/** @var iterable<Key> */
	private $keys;
	
	/** @var string */
	private $nextApplicationKeyId;

	public function __construct(
		array $keys,
		?string $nextApplicationKeyId = null
	) {
			$this->keys                 = static::createObjectIterable(Key::class, $keys);
			$this->nextApplicationKeyId = $nextApplicationKeyId;
	}

	/**
	 * Get the value of keys.
	 */ 
	public function getKeys(): Generator
	{
		return $this->keys;
	}

	public function getKeysArray(): iterable
	{
		return iterator_to_array($this->getKeys());
	}

	/**
	 * Get the value of nextApplicationKeyId.
	 */ 
	public function getNextApplicationKeyId(): ?string
	{
		return $this->nextApplicationKeyId;
	}

	/**
	 * @inheritdoc
	 * 
	 * @return KeyList
	 */
	public static function fromResponse(ResponseInterface $response): KeyList
	{
		$responseData = Utils::jsonDecode((string) $response->getBody(), true);

		return new KeyList(
			$responseData[Key::ATTRIBUTE_KEYS],
			$responseData[Key::ATTRIBUTE_NEXT_APPLICATION_KEY_ID]
		);
	}
}