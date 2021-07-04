<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Class;

use Zaxbux\BackblazeB2\Trait\ObjectIterableTrait;

/** @package Zaxbux\BackblazeB2\Class */
abstract class ListResponseBase extends ResponseBase {
	use ObjectIterableTrait;
}
