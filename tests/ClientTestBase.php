<?php

declare(strict_types=1);

namespace tests;

use BlastCloud\Guzzler\Expectation;
use BlastCloud\Guzzler\UsesGuzzler;
use PHPUnit\Framework\TestCase;
use Zaxbux\BackblazeB2\Client;
use tests\Traits\EndpointHelpersTrait;
use Zaxbux\BackblazeB2\Http\Endpoint;
use Zaxbux\BackblazeB2\Utils;

abstract class ClientTestBase extends TestCase
{
	use UsesGuzzler;
	use EndpointHelpersTrait;

	/**
	 * The account authorization object for all client tests.
	 */
	protected const ACCOUNT_AUTHORIZATION = [
		"accountId" => "000000000000bb8",
		"allowed" => [
			'bucketId' => 'bucket_id',
			'bucketName' => 'bucket_name'
		],
		"apiUrl" => "https://apiNNN.backblaze.com.test",
		"authorizationToken" => "0_0000000000008f80000000000_zzzzzzzz_zzzzzz_acct_zzzzzzzzzzzzzzzzzzzzzzzzzzzz",
		"downloadUrl" => "https://fNNN.backblaze.com.test",
		"recommendedPartSize" => 100000000,
		"absoluteMinimumPartSize" => 5000000,
		"s3ApiUrl" => "https://s3.us-west-NNN.backblazeb2.com"
	];

	/** @var \Zaxbux\BackblazeB2\Client */
	protected $client;

	protected function setUp(): void
	{
		parent::setUp();

		$this->client = new Client($this->clientInit());
		
		$this->guzzler->queueResponse(
			MockResponse::json(static::ACCOUNT_AUTHORIZATION),
		);

		$this->client->refreshAccountAuthorization();

		Expectation::macro('withAuthorizationToken', function (Expectation $e, $token) {
			return $e->withHeaders([
				'content-type' => 'application/json',
				'accept' => 'application/json',
				
			]);
			$e->withHeader('authorization', $token);
		});
		
		$this->guzzler->expects($this->any())->withAuthorizationToken(static::ACCOUNT_AUTHORIZATION['authorizationToken']);

		$this->afterSetUp();
	}

	protected function afterSetUp() {
		$this->guzzler->expects($this->atLeast(0))
		->get(Client::BASE_URI . Client::B2_API_VERSION . Endpoint::AUTHORIZE_ACCOUNT)
		->withHeader('Authorization', 'Basic MDAwMDAwMDAwMDAwYmI4MDAwMDAwMDAwMDphYmNkZWZnaGlqa2xtbm9wcXJzdHV2d3h5ejAxMjM0')
		->withHeader('User-Agent', Utils::getUserAgent('app_name'));
	}

	protected function clientInit() {
		return [
			'applicationName'  => 'app_name',
			'applicationKeyId' => '000000000000bb80000000000',
			'applicationKey'   => 'abcdefghijklmnopqrstuvwxyz01234',
			'handler'          => $this->guzzler->getHandlerStack()
		];
	}
}
