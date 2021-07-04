<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\B2\Response;

use Generator;
use Psr\Http\Message\ResponseInterface;
use Zaxbux\BackblazeB2\B2\Object\Bucket;
use Zaxbux\BackblazeB2\Classes\ListResponseBase;

use function iterator_to_array;
use function GuzzleHttp\json_decode;

/** @package Zaxbux\BackblazeB2\B2\Response */
class BucketListResponse extends ListResponseBase {
	
	/** @var iterable<Bucket> */
	private $buckets;

	public function __construct(array $buckets)
	{
		$this->buckets  = $this->createObjectIterable(Bucket::class, $buckets);
	}

	/**
	 * Get the value of buckets.
	 */ 
	public function getBuckets(): Generator
	{
		return $this->buckets;
	}

	/**
	 * Get the value of files.
	 * 
	 * @return iterable<Bucket>
	 */ 
	public function getBucketsArray(): iterable
	{
		return iterator_to_array($this->getBuckets());
	}

	/**
	 * @inheritdoc
	 * 
	 * @return BucketListResponse
	 */
	public static function create(ResponseInterface $response): BucketListResponse
	{
		$responseData = json_decode((string) $response->getBody(), true);

		return new BucketListResponse($responseData[Bucket::ATTRIBUTE_BUCKETS]);
	}
}