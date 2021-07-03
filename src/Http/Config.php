<?php

namespace Zaxbux\BackblazeB2\Http;

use Zaxbux\BackblazeB2\Client;

class Config {

	/** @var Client */
	public $client;

	/** @var array */
	public $middleware = [];

	/** @var string */
	public $baseUri = '';

	/** @var bool */
	public $useHttpErrors = false;
}