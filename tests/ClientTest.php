<?php

namespace tests;

use Zaxbux\BackblazeB2\Client;
use Zaxbux\BackblazeB2\Config;
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
		$this->guzzler->queueResponse(
			MockResponse::fromFile('authorize_account.json'),
		);
		
		$this->assertInstanceOf(AccountAuthorization::class, $this->client->authorizeAccount());
	}
}
