<?php

namespace Zaxbux\BackblazeB2\Http;

use Zaxbux\BackblazeB2\B2\Object\AccountAuthorization;

class Config {

	/** @var array */
	public $middleware = [];

	/** @var bool */
	public $useHttpErrors = false;

	public $maxRetries = 3;

	public $auth;

	public $useSSEHeaders = false;
	public $maxKeyCount  = 1000;
	public $maxFileCount = 1000;

	public function __construct(AccountAuthorization $auth = null)
	{
		$this->auth = $auth;
	}
}