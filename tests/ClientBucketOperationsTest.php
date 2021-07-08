<?php

namespace tests;

use Zaxbux\BackblazeB2\Object\Bucket;
use Zaxbux\BackblazeB2\Object\Bucket\BucketType;
use Zaxbux\BackblazeB2\Exceptions\Request\BadRequestException;
use Zaxbux\BackblazeB2\Exceptions\Request\DuplicateBucketNameException;
use Zaxbux\BackblazeB2\Http\Endpoint;

class ClientBucketOperationsTest extends ClientTestBase
{
	public function testCreateBucket()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('create_bucket_private.json'),
		);

		$this->guzzler->expects($this->once())
			->post(static::getEndpointUri(Endpoint::CREATE_BUCKET));

		$bucket = $this->client->createBucket(
			'Test bucket',
		);

		// Test that we get a private bucket back after creation
		static::assertInstanceOf(Bucket::class, $bucket);
		static::assertEquals('Test bucket', $bucket->getName());
		static::assertEquals(BucketType::PRIVATE, $bucket->getType());
	}

	public function testCreatePublicBucket()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('create_bucket_public.json'),
		);

		$this->guzzler->expects($this->once())
			->post(static::getEndpointUri(Endpoint::CREATE_BUCKET));

		// Test that we get a public bucket back after creation
		$bucket = $this->client->createBucket(
			'Test bucket',
			BucketType::PUBLIC
		);

		static::assertInstanceOf(Bucket::class, $bucket);
		static::assertEquals('Test bucket', $bucket->getName());
		static::assertEquals(BucketType::PUBLIC, $bucket->getType());
	}

	public function testBucketAlreadyExistsExceptionThrown()
	{
		$this->expectException(DuplicateBucketNameException::class);

		$this->guzzler->queueResponse(
			MockResponse::fromFile('create_bucket_exists.json', 400),
		);
		$this->client->createBucket(
			'I already exist',
			BucketType::PRIVATE
		);
	}

	public function testInvalidBucketTypeThrowsException()
	{
		$this->expectException(BadRequestException::class);

		$this->guzzler->queueResponse(
			MockResponse::fromFile('create_bucket_invalid_type.json', 400),
		);
		$this->client->createBucket(
			'Test bucket',
			'i am not valid'
		);
	}

	public function testUpdateBucketToPrivate()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('update_bucket_to_private.json'),
		);

		$bucket = $this->client->updateBucket(
			'bucketId',
			BucketType::PRIVATE
		);

		static::assertInstanceOf(Bucket::class, $bucket);
		static::assertEquals('bucketId', $bucket->getId());
		static::assertEquals(BucketType::PRIVATE, $bucket->getType());
	}

	public function testUpdateBucketToPublic()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('update_bucket_to_public.json'),
		);

		$bucket = $this->client->updateBucket(
			BucketType::PUBLIC,
			'bucketId'
		);

		static::assertInstanceOf(Bucket::class, $bucket);
		static::assertEquals('bucketId', $bucket->getId());
		static::assertEquals(BucketType::PUBLIC, $bucket->getType());
	}

	public function testList3Buckets()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('list_buckets_3.json'),
		);

		$buckets = $this->client->listBuckets()->getArrayCopy();
		static::assertIsArray($buckets);
		static::assertCount(3, $buckets);
		static::assertInstanceOf(Bucket::class, $buckets[0]);
	}

	public function testEmptyArrayWithNoBuckets()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('list_buckets_0.json'),
		);

		$buckets = $this->client->listBuckets()->getArrayCopy();
		static::assertIsArray($buckets);
		static::assertCount(0, $buckets);
	}

	public function testDeleteBucket()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('delete_bucket.json'),
		);

		$this->guzzler->expects($this->once())
			->post(static::getEndpointUri(Endpoint::DELETE_BUCKET));

		$bucket = $this->client->deleteBucket('bucketId');

		static::assertInstanceOf(Bucket::class, $bucket);
	}

	public function testDeleteBucketWithFiles()
	{
		$this->guzzler->expects($this->once())
			->post(self::getEndpointUri(Endpoint::LIST_FILE_VERSIONS));
		$this->guzzler->expects($this->exactly(3))
			->post(self::getEndpointUri(Endpoint::DELETE_FILE_VERSION));
		$this->guzzler->expects($this->once())
			->post(static::getEndpointUri(Endpoint::DELETE_BUCKET));

		$this->guzzler->queueResponse(
			MockResponse::fromFile('list_file_versions.json'),
		);

		$this->guzzler->queueMany(MockResponse::fromFile('delete_file.json'), 3);

		$this->guzzler->queueResponse(
			MockResponse::fromFile('delete_bucket.json'),
		);

		$bucket = $this->client->deleteBucket('bucketId', true);

		static::assertInstanceOf(Bucket::class, $bucket);
	}

	public function testBadJsonThrownDeletingNonExistentBucket()
	{
		$this->expectException(BadRequestException::class);

		$this->guzzler->queueResponse(
			MockResponse::fromFile('delete_bucket_non_existent.json', 400),
		);

		$this->client->deleteBucket('bucketId');
	}

	public function testBucketNotEmptyThrownDeletingNonEmptyBucket()
	{
		$this->expectException(BadRequestException::class);

		$this->guzzler->queueResponse(
			MockResponse::fromFile('bucket_not_empty.json', 400),
		);

		$this->client->deleteBucket('bucketId');
	}

	public function testGetBucketById()
	{
		$this->guzzler->expects($this->once())
			->post(static::getEndpointUri(Endpoint::LIST_BUCKETS));
		
		$this->guzzler->queueResponse(MockResponse::fromFile('get_bucket.json'));

		$bucket = $this->client->getBucketById('bucketId');

		static::assertInstanceOf(Bucket::class, $bucket);
	}

	public function testGetBucketByName()
	{
		$this->guzzler->expects($this->once())
			->post(static::getEndpointUri(Endpoint::LIST_BUCKETS));
		
		$this->guzzler->queueResponse(MockResponse::fromFile('get_bucket.json'));

		$bucket = $this->client->getBucketByName('bucket_name');

		static::assertInstanceOf(Bucket::class, $bucket);
	}
}
