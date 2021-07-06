<?php

namespace tests;

use GuzzleHttp\Psr7\Response;
use Zaxbux\BackblazeB2\Client;
use Zaxbux\BackblazeB2\Config;
use Zaxbux\BackblazeB2\Http\Exceptions\TooManyRequestsException;
use Zaxbux\BackblazeB2\Object\AccountAuthorization;

class ClientTest extends ClientTestBase
{
	protected function afterSetUp() {
		return;
	}

	public function testClient()
	{
		$this->assertInstanceOf(Client::class, $this->client);
	}

	public function testClientConfig()
	{
		$this->assertInstanceOf(Config::class, $this->client->getConfig());
	}

	public function testClientAuthorizeAccount()
	{
		$this->guzzler->expects($this->exactly(2))
			->get(Client::BASE_URI . Client::B2_API_VERSION . Endpoint::AUTHORIZE_ACCOUNT)
			->withHeader('Authorization', 'Basic MDAwMDAwMDAwMDAwYmI4MDAwMDAwMDAwMDphYmNkZWZnaGlqa2xtbm9wcXJzdHV2d3h5ejAxMjM0');

		$this->guzzler->queueResponse(
			MockResponse::json(static::ACCOUNT_AUTHORIZATION),
		);
		
		$this->assertInstanceOf(AccountAuthorization::class, $this->client->authorizeAccount());
	}

	public function testRetryMiddleware()
	{
		$this->guzzler->queueMany(new Response(429, ['Retry-After' => 1]), 4);
		$this->guzzler->queueResponse(MockResponse::json(['files' => []], 200));

		$this->client->getHttpClient()->request('POST', Endpoint::LIST_BUCKETS);

		$this->guzzler->assertLast(function ($expect) {
			return $expect->post(static::getEndpointUri(Endpoint::LIST_BUCKETS));
		});
	}

	public function testThrowsTooManyRequestsException()
	{
		$this->expectException(TooManyRequestsException::class);

		$this->guzzler->queueMany(new Response(429, ['Retry-After' => 1]), 5);

		$this->client->getHttpClient()->request('POST', static::getEndpointUri(Endpoint::LIST_BUCKETS));

		
	}
}
