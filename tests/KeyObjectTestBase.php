<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use Zaxbux\BackblazeB2\Object\Key;

abstract class KeyObjectTestBase extends TestCase
{
	protected static function getKeyInit(): array
	{
		return [
			Key::ATTRIBUTE_KEY_NAME => 'key name',
			Key::ATTRIBUTE_APPLICATION_KEY_ID => 'applicationKeyId',
			Key::ATTRIBUTE_APPLICATION_KEY => 'applicationKey',
			Key::ATTRIBUTE_CAPABILITIES => [
				'bypassGovernance',
				'deleteBuckets',
				'deleteFiles',
				'deleteKeys',
				'listAllBucketNames',
				'listBuckets',
				'listFiles',
				'listKeys',
				'readBucketEncryption',
				'readBucketRetentions',
				'readBuckets',
				'readFileLegalHolds',
				'readFileRetentions',
				'readFiles',
				'shareFiles',
				'writeBucketEncryption',
				'writeBucketRetentions',
				'writeBuckets',
				'writeFileLegalHolds',
				'writeFileRetentions',
				'writeFiles',
				'writeKeys',
			],
			Key::ATTRIBUTE_ACCOUNT_ID => 'accountId',
			Key::ATTRIBUTE_EXPIRATION_TIMESTAMP => Utils::nowInMilliseconds(),
			Key::ATTRIBUTE_BUCKET_ID => 'bucketId',
			Key::ATTRIBUTE_NAME_PREFIX => 'directory/prefix/',
			Key::ATTRIBUTE_OPTIONS => [],
		];
	}

	protected static function createKeys($count)
	{
		$keys = [];

		for ($i=0; $i < $count; $i++) {
			$keys[] = /*Key::fromArray(*/array_merge(static::getKeyInit(), [
				Key::ATTRIBUTE_APPLICATION_KEY_ID => 'applicationKeyId'.$i,
			]/*)*/);
		}

		return $keys;
	}

	protected static function isKeyObject($key): void
	{
		static::assertInstanceOf(Key::class, $key);
		static::assertIsString($key->name());
		static::assertIsString($key->applicationKeyId());
		static::assertIsString($key->applicationKey());
		static::assertIsArray($key->capabilities());
		static::assertIsString($key->accountId());
		static::assertIsInt($key->expirationTimestamp());
		static::assertIsString($key->bucketId());
		static::assertIsString($key->namePrefix());
		static::assertIsArray($key->options());
	}

	protected static function isKeyList($keyList) {
		static::assertIsIterable($keyList);

		// Get a copy of keys as an array to avoid generator issues
		$keys = $keyList->getArrayCopy();

		static::assertCount(10, $keys);
		static::assertContainsOnlyInstancesOf(Key::class, $keys);
		
		static::assertEquals(null, $keyList->nextApplicationKeyId());
	}
}
