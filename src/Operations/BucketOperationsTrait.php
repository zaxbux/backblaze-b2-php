<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Operations;

use Zaxbux\BackblazeB2\Exceptions\NotFoundException;
use Zaxbux\BackblazeB2\Http\Endpoint;
use Zaxbux\BackblazeB2\Object\AccountAuthorization;
use Zaxbux\BackblazeB2\Object\Bucket;
use Zaxbux\BackblazeB2\Object\Bucket\BucketInfo;
use Zaxbux\BackblazeB2\Response\BucketList;
use Zaxbux\BackblazeB2\Object\Bucket\BucketType;
use Zaxbux\BackblazeB2\Utils;

/** @package BackblazeB2\Operations */
trait BucketOperationsTrait
{

	/** @var \GuzzleHttp\ClientInterface */
	protected $http;

	abstract protected function accountAuthorization(): AccountAuthorization;
	
	/**
	 * Create a bucket with the given name and type.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_create_bucket.html
	 * 
	 * @b2-capability writeBuckets
	 * @b2-transaction Class C
	 *
	 * @param string          $bucketName     The name to give the new bucket.
	 * @param string          $bucketType     Either "allPublic", meaning that files in this bucket can be downloaded
	 *                                        by anybody, or "allPrivate", meaning that you need a bucket 
	 *                                        authorization token to download the files. 
	 * @param BucketInfo|null $bucketInfo     User-defined information to be stored with the bucket.
	 * @param CORSRule[]      $corsRules      The initial CORS rules for this bucket.
	 * @param LifecycleRule[] $lifecycleRules The initial lifecycle rules for this bucket.
	 */
	public function createBucket(
		string $bucketName,
		?string $bucketType = BucketType::PRIVATE,
		$bucketInfo = null,
		?array $corsRules = null,
		?array $lifecycleRules = null
	): Bucket {
		$response = $this->http->request('POST', Endpoint::CREATE_BUCKET, [
			'json' => Utils::filterRequestOptions([
				Bucket::ATTRIBUTE_ACCOUNT_ID  => $this->accountAuthorization()->getAccountId(),
				Bucket::ATTRIBUTE_BUCKET_NAME => $bucketName,
				Bucket::ATTRIBUTE_BUCKET_TYPE => $bucketType,
			], [
				Bucket::ATTRIBUTE_BUCKET_INFO     => $bucketInfo,
				Bucket::ATTRIBUTE_CORS_RULES      => $corsRules,
				Bucket::ATTRIBUTE_LIFECYCLE_RULES => $lifecycleRules,
			]),
		]);

		return Bucket::fromResponse($response);
	}

	/**
	 * Deletes the bucket specified. Only buckets that contain no version of any files can be deleted.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_delete_bucket.html
	 * 
	 * @b2-capability deleteBuckets
	 * @b2-transaction Class A
	 *
	 * @param string $bucketId  The ID of the bucket to delete.
	 * @param bool   $withFiles Delete all file versions first.
	 */
	public function deleteBucket(string $bucketId, ?bool $withFiles = false): Bucket
	{
		if ($withFiles) {
			// Delete all files from the bucket first, so that the bucket itself can be deleted.
			$this->deleteAllFileVersions($bucketId);
		}

		$response = $this->http->request('POST', Endpoint::DELETE_BUCKET, [
			'json' => [
				Bucket::ATTRIBUTE_ACCOUNT_ID => $this->accountAuthorization()->getAccountId(),
				Bucket::ATTRIBUTE_BUCKET_ID  => $bucketId
			]
		]);

		return Bucket::fromResponse($response);
	}

	/**
	 * Returns a list of bucket objects representing the buckets on the account.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_list_buckets.html
	 * 
	 * @b2-capability listBuckets
	 * @b2-transaction Class C
	 *
	 * @param string   $bucketId    When bucketId is specified, the result will be a list containing just this bucket,
	 *                              if it's present in the account, or no buckets if the account does not have a
	 *                              bucket with this ID.
	 * @param string   $bucketName  When bucketName is specified, the result will be a list containing just this bucket,
	 *                              if it's present in the account, or no buckets if the account does not have a
	 *                              bucket with this name.
	 * @param string[] $bucketTypes Filter buckets by type. Either "allPublic" or "allPrivate" or "snapshot" or "all".
	 *                              If "all" is specified, it must be the only type.
	 */
	public function listBuckets(
		?array $bucketTypes = null,
		?string $bucketId = null,
		?string $bucketName = null
	): BucketList {
		$response = $this->http->request('POST', Endpoint::LIST_BUCKETS, [
			'json' => Utils::filterRequestOptions([
				Bucket::ATTRIBUTE_ACCOUNT_ID => $this->accountAuthorization()->getAccountId(),
			], [
				Bucket::ATTRIBUTE_BUCKET_ID    => $bucketId,
				Bucket::ATTRIBUTE_BUCKET_NAME  => $bucketName,
				Bucket::ATTRIBUTE_BUCKET_TYPES => $bucketTypes,
			]),
		]);

		return BucketList::fromResponse($response);
	}

	/**
	 * Updates the type attribute of a bucket by the given ID.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_update_bucket.html
	 * 
	 * @b2-capability writeBuckets
	 * @b2-transaction Class C
	 *
	 * @param string           $bucketId       The unique ID of the bucket.
	 * @param string           $bucketType     Either "allPublic", meaning that files in this bucket can be downloaded
	 *                                         by anybody, or "allPrivate", meaning that you need a bucket
	 *                                         authorization token to download the files. 
	 * @param array            $bucketInfo     User-defined information to be stored with the bucket.
	 * @param CORSRule[]       $corsRules      The initial CORS rules for this bucket.
	 * @param LifecycleRule[]  $lifecycleRules The initial lifecycle rules for this bucket.
	 * @param int              $ifRevisionIs   When set, the update will only happen if the revision number stored in
	 *                                         the B2 service matches the one passed in.
	 */
	public function updateBucket(
		?string $bucketId = null,
		?string $bucketType = null,
		?array $bucketInfo = null,
		?array $corsRules = null,
		?array $lifecycleRules = null,
		?int $ifRevisionIs = null
	): Bucket {
		$response = $this->http->request('POST', Endpoint::UPDATE_BUCKET, [
			'json' => Utils::filterRequestOptions([
				Bucket::ATTRIBUTE_ACCOUNT_ID => $this->accountAuthorization()->getAccountId(),
				Bucket::ATTRIBUTE_BUCKET_ID  => $bucketId ?? $this->getAllowedBucketId(),
			], [
				Bucket::ATTRIBUTE_BUCKET_TYPE     => $bucketType,
				Bucket::ATTRIBUTE_BUCKET_INFO     => $bucketInfo,
				Bucket::ATTRIBUTE_CORS_RULES      => $corsRules,
				Bucket::ATTRIBUTE_LIFECYCLE_RULES => $lifecycleRules,
				Bucket::ATTRIBUTE_IF_REVISION_IS  => $ifRevisionIs,
			])
		]);

		return Bucket::fromResponse($response);
	}

	/**
	 * Get a bucket by ID.
	 * 
	 * @param string $bucketId        The ID of the bucket to fetch. Defaults to the authorized bucket, if any.
	 * @param array|null $bucketTypes Filter for bucket types returned in the list buckets response.
	 * 
	 * @throws NotFoundException 
	 */
	public function getBucketById(
		?string $bucketId = null,
		?array $bucketTypes = null
	): Bucket {
		$response = $this->listBuckets($bucketTypes, $bucketId);

		$buckets = $response->getArrayCopy();

		if (count($buckets) !== 1) {
			throw new NotFoundException(sprintf('Bucket "%s" not found.', $bucketId));
		}

		return $buckets[0];
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

		//$buckets = $response->getArrayCopy();

		if (!$response->valid()) {
			throw new NotFoundException(sprintf('Bucket "%s" not found.', $bucketName));
		}

		return $response->current();
	}
}
