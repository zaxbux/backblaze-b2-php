<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Traits;

use Zaxbux\BackblazeB2\Object\Bucket;
use Zaxbux\BackblazeB2\Response\BucketList;
use Zaxbux\BackblazeB2\Exceptions\NotFoundException;

use function sprintf;

trait BucketServiceHelpersTrait
{

	public abstract function listBuckets(
		?string $bucketId,
		?string $bucketName,
		?array $bucketTypes
	): BucketList;

	/**
	 * Get a bucket by ID.
	 * 
	 * @param string $bucketId        The ID of the bucket to fetch.
	 * @param array|null $bucketTypes Filter for bucket types returned in the list buckets response.
	 * 
	 * @throws NotFoundException 
	 */
	public function getBucketById(string $bucketId, array $bucketTypes = null): Bucket
	{
		$response = $this->listBuckets($bucketId, null, $bucketTypes);

		if (iterator_count($response->getBuckets()) !== 1) {
			throw new NotFoundException(sprintf('Bucket "%s" not found.', $bucketId));
		}

		return $response->getBuckets()[0];
	}

	/**
	 * Get a bucket by name.
	 * 
	 * @param string $bucketName      The name of the bucket to fetch.
	 * @param array|null $bucketTypes Filter for bucket types returned in the list buckets response.
	 * 
	 * @throws NotFoundException 
	 */
	public function getBucketByName(string $bucketName, array $bucketTypes = null): Bucket
	{
		$response = $this->listBuckets(null, $bucketName, $bucketTypes);

		if (iterator_count($response->getBuckets()) !== 1) {
			throw new NotFoundException(sprintf('Bucket "%s" not found.', $bucketName));
		}

		return $response->getBuckets()[0];
	}
}