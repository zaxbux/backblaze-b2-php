<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\B2\Response;

use Psr\Http\Message\ResponseInterface;
use Zaxbux\BackblazeB2\B2\Object\Bucket;
use Zaxbux\BackblazeB2\Classes\ListResponseBase;

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
	 * 
	 * @return iterable<Bucket>
	 */ 
	public function getBuckets(): iterable
	{
		return $this->buckets;
	}

	/**
	 * @inheritdoc
	 * 
	 * @return BucketListResponse
	 */
	public static function create(ResponseInterface $response): BucketListResponse
	{
		$responseData = json_decode((string) $response->getBody());

		return static($responseData->buckets);
	}
}