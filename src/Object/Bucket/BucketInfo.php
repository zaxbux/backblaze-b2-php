<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Object\Bucket;

use Zaxbux\BackblazeB2\Classes\AbstractObjectInfo;

/**
 * @link https://www.backblaze.com/b2/docs/buckets.html#bucketInfo
 * 

 */
final class BucketInfo extends AbstractObjectInfo {
	public static function fromArray(array $data): BucketInfo {
		return new BucketInfo($data);
	}
}
