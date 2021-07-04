<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Classes;

use ArrayAccess;
use JsonSerializable;

interface B2ObjectBase extends JsonSerializable, ArrayAccess
{
	public static function fromArray(array $data): B2ObjectBase;
}