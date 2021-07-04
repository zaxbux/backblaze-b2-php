<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\B2\Response;

use Psr\Http\Message\ResponseInterface;
use Zaxbux\BackblazeB2\B2\Object\Key;
use Zaxbux\BackblazeB2\Classes\ListResponseBase;

use function GuzzleHttp\json_decode;

/** @package Zaxbux\BackblazeB2\B2\Response */
class KeyListResponse extends ListResponseBase {
	
	/** @var iterable<Key> */
	private $keys;
	
	/** @var string */
	private $nextApplicationKeyId;

	public function __construct(
		array $keys,
		?string $nextApplicationKeyId = null,
	) {
			$this->keys                 = $this->createObjectIterable(Key::class, $keys);
			$this->nextApplicationKeyId = $nextApplicationKeyId;
	}

	/**
	 * Get the value of keys.
	 * 
	 * @return iterable<Key>
	 */ 
	public function getKeys(?bool $asArray = false): iterable
	{
		if ($asArray) {
			return iterator_to_array($this->keys);
		}

		return $this->keys;
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
	 * @return KeyListResponse
	 */
	public static function create(ResponseInterface $response): KeyListResponse
	{
		$responseData = json_decode((string) $response->getBody(), true);

		return new KeyListResponse(
			$responseData[Key::ATTRIBUTE_KEYS],
			$responseData[Key::ATTRIBUTE_NEXT_APPLICATION_KEY_ID]
		);
	}
}