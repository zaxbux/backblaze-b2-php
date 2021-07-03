<?php

namespace Zaxbux\BackblazeB2\B2Object;

class Key implements B2Object {
	use ProxyArrayAccessToProperties;
	use IterableFromArrayTrait;

	public const ATTRIBUTE_KEY_NAME             = 'keyName';
	public const ATTRIBUTE_APPLICATION_KEY_ID   = 'applicationKeyId';
	public const ATTRIBUTE_CAPABILITIES         = 'capabilities';
	public const ATTRIBUTE_EXPIRATION_TIMESTAMP = 'expirationTimestamp';
	public const ATTRIBUTE_NAME_PREFIX          = 'namePrefix';
	public const ATTRIBUTE_OPTIONS              = 'options';

	public static function fromArray(array $data): Key {
		return new Key();
	}

	public function jsonSerialize(): array
	{
		return [
			
		];
	}
}