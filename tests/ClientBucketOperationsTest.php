<?php

namespace tests;

use Zaxbux\BackblazeB2\Object\Bucket;
use Zaxbux\BackblazeB2\Object\Bucket\BucketType;
use Zaxbux\BackblazeB2\Exceptions\B2APIException;
use Zaxbux\BackblazeB2\Exceptions\BadRequestException;
use Zaxbux\BackblazeB2\Exceptions\DuplicateBucketNameException;

class ClientBucketOperationsTest extends ClientTestBase
{
	public function testCreateBucket()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('create_bucket_private.json'),
		);

		$this->guzzler->expects($this->once())
			->post(Endpoint::CREATE_BUCKET);

		$bucket = $this->client->createBucket(
			'Test bucket',
		);

		// Test that we get a private bucket back after creation
		$this->assertInstanceOf(Bucket::class, $bucket);
		$this->assertEquals('Test bucket', $bucket->getName());
		$this->assertEquals(BucketType::PRIVATE, $bucket->getType());
	}

	public function testCreatePublicBucket()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('create_bucket_public.json'),
		);

		$this->guzzler->expects($this->once())
			->post(Endpoint::CREATE_BUCKET);

		// Test that we get a public bucket back after creation
		$bucket = $this->client->createBucket(
			'Test bucket',
			BucketType::PUBLIC
		);

		$this->assertInstanceOf(Bucket::class, $bucket);
		$this->assertEquals('Test bucket', $bucket->getName());
		$this->assertEquals(BucketType::PUBLIC, $bucket->getType());
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

		$this->assertInstanceOf(Bucket::class, $bucket);
		$this->assertEquals('bucketId', $bucket->getId());
		$this->assertEquals(BucketType::PRIVATE, $bucket->getType());
	}

	public function testUpdateBucketToPublic()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('update_bucket_to_public.json'),
		);

		$bucket = $this->client->updateBucket(
			'bucketId',
			BucketType::PUBLIC
		);

		$this->assertInstanceOf(Bucket::class, $bucket);
		$this->assertEquals('bucketId', $bucket->getId());
		$this->assertEquals(BucketType::PUBLIC, $bucket->getType());
	}

	public function testList3Buckets()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('list_buckets_3.json'),
		);

		$buckets = $this->client->listBuckets()->getBucketsArray();
		$this->assertIsArray($buckets);
		$this->assertCount(3, $buckets);
		$this->assertInstanceOf(Bucket::class, $buckets[0]);
	}

	public function testEmptyArrayWithNoBuckets()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('list_buckets_0.json'),
		);

		$buckets = $this->client->listBuckets()->getBucketsArray();
		$this->assertIsArray($buckets);
		$this->assertCount(0, $buckets);
	}

	public function testDeleteBucket()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('delete_bucket.json'),
		);

		$this->guzzler->expects($this->once())
			->post('/b2_delete_bucket');

		$this->assertInstanceOf(Bucket::class, $this->client->deleteBucket(
			'bucketId'
		));
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
		$this->expectException(B2APIException::class);

		$this->guzzler->queueResponse(
			MockResponse::fromFile('bucket_not_empty.json', 400),
		);

		$this->client->deleteBucket('bucketId');
	}
}
