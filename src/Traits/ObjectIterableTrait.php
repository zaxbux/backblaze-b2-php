<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Traits;

use Generator;
use RuntimeException;
use Zaxbux\BackblazeB2\Interfaces\B2ObjectInterface;

/** @package BackblazeB2\Traits */
trait ObjectIterableTrait
{
	/**
	 * 
	 * @param string $object
	 * @param array  $data
	 * 
	 * @return iterable<B2ObjectInterface>
	 * 
	 * @throws RuntimeException
	 */
	public static function createObjectIterable(string $object, array $data): Generator
	{
		if (!method_exists($object, 'fromArray')) {
			throw new RuntimeException($object .' does not implement fromArray() method');
		}

		foreach ($data as $init) {
			yield $object::fromArray($init);
		}
	}
}