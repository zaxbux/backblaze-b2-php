<?php

declare(strict_types=1);

namespace tests;

use GuzzleHttp\Psr7\Response;
use Zaxbux\BackblazeB2\Object\Bucket;
use Zaxbux\BackblazeB2\Response\BucketList;

class BucketObjectTest extends BucketObjectTestBase
{
	public function testNewBucketObject()
	{
		static::isBucketObject(new Bucket(...array_values(static::getBucketInit())));
	}

	public function testCreateBucketObjectFromArray()
	{
		static::isBucketObject(Bucket::fromArray(static::getBucketInit()));
	}

	public function testBucketList()
	{
		$buckets = static::createBuckets(100);

		$bucketList = new BucketList($buckets);

		static::isBucketList($bucketList);
	}

	public function testBucketListFromResponse()
	{
		$bucketList = BucketList::create(new Response(200, [], json_encode([
			Bucket::ATTRIBUTE_BUCKETS => static::createBuckets(100),
		])));

		static::isBucketList($bucketList);
	}
}
