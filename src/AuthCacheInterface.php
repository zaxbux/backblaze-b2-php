<?php

namespace Zaxbux\BackblazeB2;

interface AuthCacheInterface {

	/**
	 * The maximum number of seconds to cache the authorization token
	 */
	const EXPIRES = 86400;

	/**
	 * Caches authentication data
	 * @param $key
	 * @param $authData
	 */
	public function cache($key, $token);

	/**
	 * Returns the authorization token for the given key
	 *
	 * @param $key
	 * @return array|null
	 */
	public function get($key);
}