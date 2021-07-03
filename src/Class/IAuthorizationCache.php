<?php

namespace Zaxbux\BackblazeB2\Client;

/** @package Zaxbux\BackblazeB2\Client */
interface IAuthorizationCache {

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
	public function put(int|string $key, AccountAuthorization $authorization): void;

	/**
	 * Returns the account authorization given key.
	 *
	 * @param int|string $key
	 * @return null|AccountAuthorization
	 */
	public function get(int|string $key): ?AccountAuthorization;
}