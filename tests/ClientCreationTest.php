<?php

namespace tests;

use PHPUnit\Framework\TestCase;
use Zaxbux\BackblazeB2\Client;
use Zaxbux\BackblazeB2\Config;

class ClientCreationTest extends TestCase
{
	public function testNewClient()
	{
		$this->assertInstanceOf(Client::class, new Client([
			'000000000000bb80000000000',
			'abcdefghijklmnopqrstuvwxyz01234',
		]));
	}

	public function testNewClientWithOptionsArray()
	{
		$client = new Client([
			'applicationName'  => 'app_name',
			'applicationKeyId' => '000000000000bb80000000000',
			'applicationKey'   => 'abcdefghijklmnopqrstuvwxyz01234',
		]);

		$this->assertInstanceOf(Client::class, $client);
		$this->assertEquals('app_name', $client->getConfig()->applicationName());
	}

	public function testNewClientWithConfig()
	{
		$client = new Client(new Config(
			'000000000000bb80000000000',
			'abcdefghijklmnopqrstuvwxyz01234',
			[
				'applicationName' => 'app_name',
			]
		));

		$this->assertInstanceOf(Client::class, $client);
		$this->assertEquals('app_name', $client->getConfig()->applicationName());
	}

	public function testCreateClient()
	{
		$this->assertInstanceOf(Client::class, Client::create([
			'applicationKeyId' => '000000000000bb80000000000',
			'applicationKey'   => 'abcdefghijklmnopqrstuvwxyz01234',
		]));
	}
}
