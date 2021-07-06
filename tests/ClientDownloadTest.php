<?php

namespace tests;

use Zaxbux\BackblazeB2\Exceptions\B2APIException;
use Zaxbux\BackblazeB2\Exceptions\NotFoundException;

class ClientDownloadTest extends ClientTestBase
{
	public function testGetDownloadAuthorization()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('get_download_authorization.json'),
		);

		$authorization = $this->client->getDownloadAuthorization(
			'bucketId',
			'public',
			60
		);

		$this->assertEquals('downloadAuthToken', $authorization->getAuthorizationToken());
	}

	public function testDownloadByIdWithoutSavePath()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('download_content'),
		);

		$fileContent = $this->client->downloadFileById('fileId')->getContents();

		$this->assertEquals($fileContent, 'The quick brown fox jumps over the lazy dog');
	}

	public function testDownloadByIdWithSavePath()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('download_content'),
		);

		$this->client->downloadFileById('fileId', null, __DIR__ . '/test.txt');

		$this->assertFileExists(__DIR__ . '/test.txt');
		$this->assertEquals('The quick brown fox jumps over the lazy dog', file_get_contents(__DIR__ . '/test.txt'));

		unlink(__DIR__ . '/test.txt');
	}

	public function testDownloadingByIncorrectIdThrowsException()
	{
		$this->expectException(B2APIException::class);

		$this->guzzler->queueResponse(
			MockResponse::fromFile('download_by_incorrect_id.json', 400),
		);

		$this->client->downloadFileById('incorrect');
	}

	public function testDownloadByPathWithoutSavePath()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('download_content'),
		);

		$fileContent = $this->client->downloadFileByName('test.txt', 'test-bucket')->getContents();

		$this->assertEquals($fileContent, 'The quick brown fox jumps over the lazy dog');
	}

	public function testDownloadByPathWithSavePath()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('download_content'),
		);

		$this->client->downloadFileByName('test.txt', 'test-bucket', null, __DIR__ . '/test.txt');

		$this->assertFileExists(__DIR__ . '/test.txt');
		$this->assertEquals('The quick brown fox jumps over the lazy dog', file_get_contents(__DIR__ . '/test.txt'));

		unlink(__DIR__ . '/test.txt');
	}

	public function testDownloadingByIncorrectPathThrowsException()
	{
		$this->expectException(NotFoundException::class);

		$this->guzzler->queueResponse(
			MockResponse::fromFile('download_by_incorrect_path.json', 400),
		);

		$this->client->downloadFileByName('path/to/incorrect/file.txt', 'test-bucket');
	}
	
}
