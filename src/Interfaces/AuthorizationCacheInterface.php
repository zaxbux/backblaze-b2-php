<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Interfaces;

use Zaxbux\BackblazeB2\Object\AccountAuthorization;


interface AuthorizationCacheInterface
{

	/**
	 * The maximum number of seconds to cache the authorization token
	 */
	const EXPIRES = 86400;

	/**
	 * Caches the account authorization.
	 * 
	 * @param int|string $key
	 * @param AccountAuthorization $authorization
	 */
	public function put($key, AccountAuthorization $authorization): void;

	/**
	 * Returns the account authorization given key.
	 *
	 * @param int|string $key
	 * @return null|AccountAuthorization
	 */
	public function get($key): ?AccountAuthorization;
}