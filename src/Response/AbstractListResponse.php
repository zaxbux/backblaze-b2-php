<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Response;

use Zaxbux\BackblazeB2\Traits\HydrateFromResponseTrait;
use Zaxbux\BackblazeB2\Traits\ObjectIterableTrait;
use Zaxbux\BackblazeB2\Traits\ResponseTrait;

/** @package BackblazeB2\Response */
abstract class AbstractListResponse extends \ArrayIterator {
	use ResponseTrait;
	use ObjectIterableTrait;
	use HydrateFromResponseTrait;

	public function getArrayCopy(): array
	{
		return iterator_to_array($this);
	}

	public function mergeList(AbstractListResponse $list): void {
		foreach ($list as $value) {
			$this->append($value);
		}
	}
}
