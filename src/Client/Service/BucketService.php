<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Client\Service;

use Zaxbux\BackblazeB2\B2\Object\Bucket;
use Zaxbux\BackblazeB2\B2\Object\BucketInfo;
use Zaxbux\BackblazeB2\B2\Response\BucketListResponse;
use Zaxbux\BackblazeB2\B2\Type\BucketType;
use Zaxbux\BackblazeB2\Classes\ServiceBase;
use Zaxbux\BackblazeB2\Traits\BucketServiceHelpersTrait;

trait BucketService
{
	use BucketServiceHelpersTrait;

	abstract public function getAccountAuthorization();

	public abstract function deleteAllFileVersions(
		string $bucketId,
		?string $prefix = '',
		?string $delimiter = null,
		?string $startFileName = null,
		?string $startFileId = null,
		?bool $bypassGovernance = false
	): void;
	
	/**
	 * Create a bucket with the given name and type.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_create_bucket.html
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
		$response = $this->guzzle->request('POST', '/b2_create_bucket', [
			'json' => ServiceBase::filterRequestOptions([
				Bucket::ATTRIBUTE_ACCOUNT_ID  => $this->getAccountAuthorization()->getAccountId(),
				Bucket::ATTRIBUTE_BUCKET_NAME => $bucketName,
				Bucket::ATTRIBUTE_BUCKET_TYPE => $bucketType,
			], [
				Bucket::ATTRIBUTE_BUCKET_INFO     => $bucketInfo,
				Bucket::ATTRIBUTE_CORS_RULES      => $corsRules,
				Bucket::ATTRIBUTE_LIFECYCLE_RULES => $lifecycleRules,
			]),
		]);

		return Bucket::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Deletes the bucket specified. Only buckets that contain no version of any files can be deleted.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_delete_bucket.html 
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

		$response = $this->guzzle->request('POST', '/b2_delete_bucket', [
			'json' => [
				Bucket::ATTRIBUTE_ACCOUNT_ID => $this->getAccountAuthorization()->getAccountId(),
				Bucket::ATTRIBUTE_BUCKET_ID  => $bucketId
			]
		]);

		return Bucket::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Returns a list of bucket objects representing the buckets on the account.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_list_buckets.html
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
		?string $bucketId = null,
		?string $bucketName = null,
		?array $bucketTypes = null
	): BucketListResponse {
		$response = $this->guzzle->request('POST', '/b2_list_buckets', [
			'json' => ServiceBase::filterRequestOptions([
				Bucket::ATTRIBUTE_ACCOUNT_ID => $this->getAccountAuthorization()->getAccountId(),
			], [
				Bucket::ATTRIBUTE_BUCKET_ID    => $bucketId,
				Bucket::ATTRIBUTE_BUCKET_NAME  => $bucketName,
				Bucket::ATTRIBUTE_BUCKET_TYPES => $bucketTypes,
			]),
		]);

		return BucketListResponse::create($response);
	}

	/**
	 * Updates the type attribute of a bucket by the given ID.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_update_bucket.html
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
		string $bucketId,
		?string $bucketType = null,
		?array $bucketInfo = null,
		?array $corsRules = null,
		?array $lifecycleRules = null,
		?int $ifRevisionIs = null
	): Bucket {
		$response = $this->guzzle->request('POST', '/b2_update_bucket', [
			'json' => ServiceBase::filterRequestOptions([
				Bucket::ATTRIBUTE_ACCOUNT_ID => $this->getAccountAuthorization()->getAccountId(),
				Bucket::ATTRIBUTE_BUCKET_ID  => $bucketId,
			], [
				Bucket::ATTRIBUTE_BUCKET_TYPE     => $bucketType,
				Bucket::ATTRIBUTE_BUCKET_INFO     => $bucketInfo,
				Bucket::ATTRIBUTE_CORS_RULES      => $corsRules,
				Bucket::ATTRIBUTE_LIFECYCLE_RULES => $lifecycleRules,
				Bucket::ATTRIBUTE_IF_REVISION_IS  => $ifRevisionIs,
			])
		]);

		return Bucket::fromArray(json_decode((string) $response->getBody(), true));
	}
}
