<?php

declare(strict_types=1);

namespace tests;

use BlastCloud\Guzzler\UsesGuzzler;
use PHPUnit\Framework\TestCase;
use Zaxbux\BackblazeB2\Client;

abstract class ClientTestBase extends TestCase
{
	use UsesGuzzler;

	/** @var \Zaxbux\BackblazeB2\Client */
	protected $client;

	protected function setUp(): void
	{
		parent::setUp();

		$this->client = new Client($this->clientInit());
		
		$this->guzzler->queueResponse(
			MockResponse::fromFile('authorize_account.json'),
		);

		$this->client->refreshAccountAuthorization();

		$this->afterSetUp();
	}

	protected function afterSetUp() {
		$this->guzzler->expects($this->once())->get(Client::BASE_URI . Client::B2_API_VERSION . Endpoint::AUTHORIZE_ACCOUNT);
	}

	protected function clientInit() {
		return [
			'applicationKeyId' => 'testId',
			'applicationKey'   => 'testKey',
			'handler'          => $this->guzzler->getHandlerStack()
			//'maxRetries'       => 0,
		];
	}
}
