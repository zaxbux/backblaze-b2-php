<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Traits;

use RuntimeException;
use Zaxbux\BackblazeB2\Interfaces\B2ObjectInterface;

trait ObjectIterableTrait
{
	//abstract public static function fromArray(array $data): B2ObjectInterface;

	/**
	 * 
	 * @param string $object
	 * @param array  $data
	 * 
	 * @return iterable<B2ObjectInterface>
	 * 
	 * @throws RuntimeException
	 */
	public static function createObjectIterable(string $object, array $data): iterable
	{
		if (!method_exists($object, 'fromArray')) {
			throw new RuntimeException($object .' does not implement fromArray() method');
		}

		foreach ($data as $init) {
			yield $object::fromArray($init);
		}
	}
}