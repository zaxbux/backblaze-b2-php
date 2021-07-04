<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\B2\Object;

use Zaxbux\BackblazeB2\Classes\ObjectInfoBase;

/**
 * @link https://www.backblaze.com/b2/docs/buckets.html#bucketInfo
 * 
 * @package Zaxbux\BackblazeB2\B2\Object
 */
final class BucketInfo extends ObjectInfoBase {
	public static function fromArray(array $data): BucketInfo {
		return new BucketInfo($data);
	}
}
