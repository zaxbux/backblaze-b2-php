<?php

namespace Zaxbux\BackblazeB2\Http;

use Zaxbux\BackblazeB2\B2\Object\AccountAuthorization;

class Config {

	public $auth;

	/** @var array */
	public $middleware = [];

	/** @var bool */
	public $useHttpErrors = false;

	/**
	 * Number of times to retry an API call before throwing an exception.
	 * @var int
	 */
	public $maxRetries = 3;

	/**
	 * Download files with Server-Side Encryption headers instead of using query parameters.
	 * @var false
	 */
	public $useSSEHeaders = false;

	/**
	 * Maximum number of application keys to return per call.
	 * @var int
	 */
	public $maxKeyCount  = 1000;

	/**
	 * Maximum number of files to return per call.
	 * @var int
	 */
	public $maxFileCount = 1000;

	/**
	 * Size limit to determine if the upload will use the large-file process.
	 * @var int
	 */
	public $largeFileUploadCustomMinimum = null; //200 * 1024 * 1024;

	public function __construct(AccountAuthorization $auth = null)
	{
		$this->auth = $auth;
	}
}