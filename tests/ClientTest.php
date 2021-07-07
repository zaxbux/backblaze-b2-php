<?php

namespace tests;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Zaxbux\BackblazeB2\Client;
use Zaxbux\BackblazeB2\Config;
use Zaxbux\BackblazeB2\Http\Exceptions\TooManyRequestsException;
use Zaxbux\BackblazeB2\Object\AccountAuthorization;

class ClientTest extends ClientTestBase
{
	public function testClient()
	{
		$this->assertInstanceOf(Client::class, $this->client);
		$this->assertInstanceOf(Config::class, $this->client->getConfig());
		$this->assertInstanceOf(ClientInterface::class, $this->client->getHttpClient());
		$this->assertInstanceOf(AccountAuthorization::class, $this->client->accountAuthorization());
		$this->assertEquals('bucket_id', $this->client->getAllowedBucketId());
		$this->assertEquals('bucket_name', $this->client->getAllowedBucketName());
	}

	/*
	public function testRetryMiddleware()
	{
		$this->guzzler->queueMany(new Response(429, ['Retry-After' => 1]), 4);
		$this->guzzler->queueResponse(MockResponse::json(['files' => []], 200));

		$this->client->getHttpClient()->request('POST', Endpoint::LIST_BUCKETS);

		$this->guzzler->assertLast(function ($expect) {
			return $expect->post(static::getEndpointUri(Endpoint::LIST_BUCKETS));
		});
	}
	*/

	/*
	public function testThrowsTooManyRequestsException()
	{
		$this->expectException(TooManyRequestsException::class);

		$this->guzzler->queueMany(new Response(429, ['Retry-After' => 1]), 5);

		$this->client->getHttpClient()->request('POST', static::getEndpointUri(Endpoint::LIST_BUCKETS));
	}
	*/
}
