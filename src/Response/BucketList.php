<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Response;

use Iterator;
use Psr\Http\Message\ResponseInterface;
use Zaxbux\BackblazeB2\Object\Bucket;
use Zaxbux\BackblazeB2\Utils;

use function iterator_to_array;

/** @package Zaxbux\BackblazeB2\Response */
class BucketList extends AbstractListResponse {
	
	/** @var iterable<Bucket> */
	private $buckets;

	public function __construct(array $buckets)
	{
		$this->buckets  = static::createObjectIterable(Bucket::class, $buckets);
	}

	/**
	 * Get the value of buckets.
	 */ 
	public function getBuckets(): Iterator
	{
		return $this->buckets;
	}

	/**
	 * Get the value of files.
	 * 
	 * @return iterable<Bucket>
	 */ 
	public function getBucketsArray(): array
	{
		return iterator_to_array($this->getBuckets());
	}

	/**
	 * @inheritdoc
	 * 
	 * @return BucketList
	 */
	public static function fromResponse(ResponseInterface $response): BucketList
	{
		$buckets = Utils::jsonDecode((string) $response->getBody())[Bucket::ATTRIBUTE_BUCKETS];

		return new BucketList($buckets);
	}
}