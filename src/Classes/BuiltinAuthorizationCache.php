<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Classes;

use Zaxbux\BackblazeB2\Interfaces\AuthorizationCacheInterface;
use Zaxbux\BackblazeB2\Object\AccountAuthorization;

/**
 * A builtin authorization cache.
 * 
 * @package Zaxbux\BackblazeB2\Classes
 */
class BuiltinAuthorizationCache implements AuthorizationCacheInterface
{
	/** @var array */
	private $storage;

	public function put($key, AccountAuthorization $authorization): void
	{
		$this->storage[(string) $key] = $authorization;
	}

	public function get($key): ?AccountAuthorization
	{
		return $this->storage[(string) $key] ?? null;
	}
}
