<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use Zaxbux\BackblazeB2\Object\Bucket;
use Zaxbux\BackblazeB2\Object\Bucket\BucketInfo;

abstract class BucketObjectTestBase extends TestCase
{
	protected static function getBucketInit(): array
	{
		return [
			Bucket::ATTRIBUTE_BUCKET_ID => 'bucketId',
			Bucket::ATTRIBUTE_BUCKET_NAME => 'Bucket name',
			Bucket::ATTRIBUTE_BUCKET_TYPE => 'allPrivate',
			Bucket::ATTRIBUTE_BUCKET_INFO => [
				'Cache-Control' => 'max-age=3600',
			],
			Bucket::ATTRIBUTE_ACCOUNT_ID => 'accountId',
			Bucket::ATTRIBUTE_CORS_RULES => [],
			Bucket::ATTRIBUTE_DEFAULT_SSE => [],
			Bucket::ATTRIBUTE_FILE_LOCK_CONFIG => [],
			Bucket::ATTRIBUTE_LIFECYCLE_RULES => [],
			Bucket::ATTRIBUTE_REVISION => 8,
			Bucket::ATTRIBUTE_OPTIONS => [],
		];
	}

	protected static function createBuckets($count)
	{
		$buckets = [];

		for ($i=0; $i < $count; $i++) {
			$buckets[] = array_merge(static::getBucketInit(), [
				Bucket::ATTRIBUTE_BUCKET_ID => 'bucketId'.$i,
			]);
		}

		return $buckets;
	}

	protected static function isBucketObject($bucket): void
	{
		static::assertInstanceOf(Bucket::class, $bucket);
		static::assertIsString($bucket->getId());
		static::assertIsString($bucket->getName());
		static::assertIsString($bucket->getType());
		static::assertInstanceOf(BucketInfo::class, $bucket->getInfo());
		static::assertEquals('max-age=3600', $bucket->getInfo()->get('Cache-Control'));
		static::assertIsString($bucket->getAccountId());
		static::assertIsArray($bucket->getCorsRules());
		static::assertIsArray($bucket->getLifecycleRules());
		static::assertIsArray($bucket->getDefaultServerSideEncryption());
		static::assertIsArray($bucket->getFileLockConfiguration());
		static::assertIsArray($bucket->getOptions());
		static::assertIsInt($bucket->getRevision());
		static::assertEquals(8, $bucket->getRevision());
	}

	protected static function isBucketList($bucketList) {
		static::assertIsIterable($bucketList);

		// Get a copy of Buckets as an array to avoid generator issues
		$buckets = $bucketList->getArrayCopy();

		static::assertCount(100, $buckets);
		static::assertContainsOnlyInstancesOf(Bucket::class, $buckets);
	}
}
