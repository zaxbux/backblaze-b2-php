<?php

namespace Zaxbux\BackblazeB2\Class;

trait IterableFromArrayTrait {
	abstract public static function fromArray(array $data): B2ObjectBase;

	/**
	 * 
	 * @param array  $data
	 * @return iterable<B2ObjectBase>
	 */
	public static function iterableFromArray(array $data): iterable
	{
		foreach ($data as $init) {
			yield static::fromArray($init);
		}
	}
}