<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Interfaces;

use ArrayAccess;
use JsonSerializable;

interface B2ObjectInterface extends JsonSerializable, ArrayAccess
{
	public static function fromArray(array $data): B2ObjectInterface;
}