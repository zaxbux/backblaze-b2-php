<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Helpers;

use BadMethodCallException;
use Zaxbux\BackblazeB2\Object\Bucket;
use Zaxbux\BackblazeB2\Object\Bucket\BucketType;
use Zaxbux\BackblazeB2\Response\BucketList;
use Zaxbux\BackblazeB2\Response\FileList;

/** @package BackblazeB2\Helpers */
class BucketOperationsHelper extends AbstractHelper {

	/** @var \Zaxbux\BackblazeB2\Object\Bucket */
	private $bucket;

	/**
	 * Specify which bucket to preform operations on. Must call this method before using `update()` or `delete()`.
	 * @param null|string|Bucket $bucket 
	 * @return BucketOperationsHelper 
	 */
	public function withBucket($bucket = null): BucketOperationsHelper
	{
		if ($bucket instanceof Bucket) {
			$this->bucket = $bucket;
		}

		// Only the bucketId is required for helper methods
		if (is_string($bucket)) {
			$this->bucket = new Bucket($bucket, '', '');
		}

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
		?array $lifecycleRules = null,
		?bool $fileLockEnabled = false,
		?array $defaultSSE = null
	): Bucket {
		return $this->client->createBucket(
			$name,
			$type,
			$info,
			$corsRules,
			$lifecycleRules,
			$fileLockEnabled,
			$defaultSSE
		);
	}

	public function delete(?bool $withFiles = false): Bucket
	{
		$this->assertBucketIsSet();
		$this->bucket = $this->client->deleteBucket($this->bucket->getId(), $withFiles);
		return $this->bucket;
	}

	public function update(
		?array $info = null,
		?string $type = null,
		?array $corsRules = null,
		?array $lifecycleRules = null,
		?array $defaultRetention = null,
		?array $defaultSSE = null,
		?int $ifRevisionIs = null
	): Bucket {
		$this->assertBucketIsSet();
		$this->bucket = $this->client->updateBucket(
			$this->bucket->getId(),
			$type,
			$info,
			$corsRules,
			$lifecycleRules,
			$defaultRetention,
			$defaultSSE,
			$ifRevisionIs
		);
		return $this->bucket;
	}

	public function listFileNames(
		?string $prefix = null,
		?string $delimiter = null,
		?string $startFileName = null,
		?int $maxFileCount = null
	): FileList {
		$this->assertBucketIsSet();
		return $this->client->listFileNames(
			$this->bucket->getId(),
			$prefix,
			$delimiter,
			$startFileName,
			$maxFileCount
		);
	}

	public function listFileVersions(
		?string $prefix = null,
		?string $delimiter = null,
		?string $startFileName = null,
		?string $startFileId = null,
		?int $maxFileCount = null
	): FileList {
		$this->assertBucketIsSet();
		return $this->client->listFileVersions(
			$this->bucket->getId(),
			$prefix,
			$delimiter,
			$startFileName,
			$startFileId,
			$maxFileCount
		);
	}

	public function listAllFileNames(
		?string $prefix = null,
		?string $delimiter = null,
		?string $startFileName = null
	): FileList {
		$this->assertBucketIsSet();
		return $this->client->listAllFileNames(
			$this->bucket->getId(),
			$prefix,
			$delimiter,
			$startFileName,
		);
	}

	public function listAllFileVersions(
		?string $prefix = null,
		?string $delimiter = null,
		?string $startFileName = null,
		?string $startFileId = null
	): FileList {
		$this->assertBucketIsSet();
		return $this->client->listAllFileNames(
			$this->bucket->getId(),
			$prefix,
			$delimiter,
			$startFileName,
			$startFileId,
		);
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