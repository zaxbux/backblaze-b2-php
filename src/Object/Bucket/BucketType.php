<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Object\Bucket;

/** @package Zaxbux\BackblazeB2\Object\Bucket */
final class BucketType
{
	public const ALL      = 'all';
	public const PUBLIC   = 'allPublic';
	public const PRIVATE  = 'allPrivate';
	public const SNAPSHOT = 'snapshot';
}
