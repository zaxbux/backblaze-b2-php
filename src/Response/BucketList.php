<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Response;

use Zaxbux\BackblazeB2\Object\Bucket;

/** @package BackblazeB2\Response */
class BucketList extends AbstractListResponse {

	public const ATTRIBUTE_BUCKETS          = 'buckets';

	public function current(): Bucket
	{
		$value = parent::current();
		return $value instanceof Bucket ? $value : Bucket::fromArray($value);
	}

	protected static function fromArray($data): BucketList
	{
		return new static($data[static::ATTRIBUTE_BUCKETS]);
	}
}