<?php

declare(strict_types=1);

namespace tests\Traits;

use Zaxbux\BackblazeB2\Client;
use Zaxbux\BackblazeB2\Utils;

trait EndpointHelpersTrait
{
	protected $endpointUriBase;
	
	protected static function getEndpointUri($endpoint): string
	{
		$base = static::$endpointUriBase ?? Client::B2_API_VERSION;
		
		return Utils::joinPaths($base, $endpoint);
	}
}