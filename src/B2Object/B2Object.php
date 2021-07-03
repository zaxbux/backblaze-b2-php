<?php

namespace Zaxbux\BackblazeB2\B2Object;

use JsonSerializable;
use ArrayAccess;

interface B2Object extends JsonSerializable, ArrayAccess {
	public const ATTRIBUTE_ACCOUNT_ID       = 'accountId';
	public const ATTRIBUTE_BUCKET_ID        = 'bucketId';

	public static function fromArray(array $data): B2Object;
}