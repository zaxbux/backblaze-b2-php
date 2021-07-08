<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Helpers;

use BadMethodCallException;
use Zaxbux\BackblazeB2\Object\Bucket;
use Zaxbux\BackblazeB2\Object\Bucket\BucketType;
use Zaxbux\BackblazeB2\Response\BucketList;

/** @package BackblazeB2\Helpers */
class BucketOperationsHelper extends AbstractHelper {

	/** @var \Zaxbux\BackblazeB2\Object\Bucket */
	private $bucket;

	/**
	 * Specify which bucket to preform operations on. Must call this method before using `update()` or `delete()`.
	 * @param null|Bucket $bucket 
	 * @return BucketOperationsHelper 
	 */
	public function withBucket(?Bucket $bucket = null): BucketOperationsHelper
	{
		$this->bucket = $bucket;
		return $this;
	}

	public function listAll(?array $types = null): BucketList
	{
		return $this->client->listBuckets($types);
	}

	public function create(
		string $name,
		?string $type = BucketType::PRIVATE,
		$info = null,
		?array $corsRules = null,
		?array $lifecycleRules = null
	): Bucket {
		return $this->client->createBucket($name, $type, $info, $corsRules, $lifecycleRules);
	}

	public function delete(?bool $withFiles = false): Bucket
	{
		static::assertBucketIsSet();
		$this->bucket = $this->client->deleteBucket($this->bucket->getId(), $withFiles);
		return $this->bucket;
	}

	public function update(
		$info = null,
		?array $corsRules = null,
		?array $lifecycleRules = null,
		?string $type = null,
		?int $ifRevisionIs = null
	): Bucket {
		static::assertBucketIsSet();
		$this->bucket = $this->client->updateBucket($this->bucket->getId(), $type, $info, $corsRules, $lifecycleRules, $ifRevisionIs);
		return $this->bucket;
	}

	public function getByName(string $name, ?array $types = null): Bucket
	{
		return $this->client->getBucketByName($name, $types);
	}

	public function getById(string $name, ?array $types = null): Bucket
	{
		return $this->client->getBucketByName($name, $types);
	}

	private function assertBucketIsSet(): void
	{
		if (!$this->bucket) {
			throw new BadMethodCallException('$bucket is not set');
		}
	}
}