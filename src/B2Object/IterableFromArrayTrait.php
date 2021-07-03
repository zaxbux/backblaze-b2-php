<?php

namespace Zaxbux\BackblazeB2\B2Object;

trait IterableFromArrayTrait {
	abstract public static function fromArray(array $data): B2Object;

	/**
	 * 
	 * @param array  $data
	 * @return iterable<B2Object>
	 */
	public static function iterableFromArray(array $data): iterable
	{
		foreach ($data as $init) {
			yield static::fromArray($init);
		}
	}
}