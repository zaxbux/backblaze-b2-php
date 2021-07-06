<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Helpers;

use GuzzleHttp\ClientInterface;
use Zaxbux\BackblazeB2\Client;
use Zaxbux\BackblazeB2\Interfaces\HelperInterface;

abstract class AbstractHelper implements HelperInterface
{
	/** @var \Zaxbux\BackblazeB2\Client */
	protected $client;

	public function __construct(Client $client)
	{
		$this->client = $client;
	}

	protected function getHttpClient(): ClientInterface
	{
		return $this->client->getHttpClient();
	}

	public static function instance(Client $client)
	{
		return new static($client);
	}
}