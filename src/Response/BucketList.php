<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Response;

use Generator;
use Psr\Http\Message\ResponseInterface;
use Zaxbux\BackblazeB2\Object\Bucket;

use function iterator_to_array;
use function GuzzleHttp\json_decode;

/** @package Zaxbux\BackblazeB2\Response */
class BucketList extends AbstractListResponse {
	
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
	 * @return BucketList
	 */
	public static function create(ResponseInterface $response): BucketList
	{
		$responseData = json_decode((string) $response->getBody(), true);

		return new BucketList($responseData[Bucket::ATTRIBUTE_BUCKETS]);
	}
}