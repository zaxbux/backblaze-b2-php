<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Trait;

use RuntimeException;
use Zaxbux\BackblazeB2\Class\B2ObjectBase;

trait ObjectIterableTrait
{
	//abstract public static function fromArray(array $data): B2ObjectBase;

	/**
	 * 
	 * @param string $object
	 * @param array  $data
	 * 
	 * @return iterable<B2ObjectBase>
	 * 
	 * @throws RuntimeException
	 */
	public static function createObjectIterable(string $object, array $data): iterable
	{
		if (!method_exists($object, 'fromArray')) {
			throw new RuntimeException('$object does not implement fromArray() method');
		}

		foreach ($data as $init) {
			yield $object::fromArray($init);
		}
	}
}