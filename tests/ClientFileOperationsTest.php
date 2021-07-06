<?php

namespace tests;

use Zaxbux\BackblazeB2\Response\FileList;
use Zaxbux\BackblazeB2\Object\File;
use Zaxbux\BackblazeB2\Exceptions\Request\BadRequestException;

class ClientFileOperationsTest extends ClientTestBase
{
	public function testListFilesHandlesMultiplePages()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('list_files_page1.json'),
			MockResponse::fromFile('list_files_page2.json'),
		);

		$files = $this->client->listAllFileNames('bucketId');

		$this->assertIsIterable($files);
		$this->assertInstanceOf(File::class, $files->current());
		$this->assertCount(1500, $files);
	}

	public function testListFilesReturnsEmptyArrayWithNoFiles()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('list_files_empty.json'),
		);

		$response = $this->client->listFileNames('bucketId');
		$this->assertInstanceOf(FileList::class, $response);
		$files = $response->getFilesArray();
		$this->assertCount(0, $files);
	}

	public function testGetFile()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('get_file.json'),
		);

		$file = $this->client->getFileById('fileId', 'bucketId');

		$this->assertInstanceOf(File::class, $file);
	}

	public function testGettingNonExistentFileThrowsException()
	{
		$this->expectException(BadRequestException::class);

		$this->guzzler->queueResponse(
			MockResponse::fromFile('get_file_non_existent.json', 400),
		);

		$this->client->getFileById('fileId', 'bucketId');
	}

	public function testDeleteFile()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('get_file.json'),
			MockResponse::fromFile('delete_file.json'),
		);

		$fileId = $this->client->getFileByName('Test file.bin', 'bucketId')->getId();

		$this->assertInstanceOf(File::class, $this->client->deleteFileVersion('Test file.bin', $fileId));
	}
	

	public function testDeleteFileRetrievesFileNameWhenNotProvided()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('list_file_versions.json'),
		);
		$this->guzzler->queueMany(MockResponse::fromFile('delete_file.json'), 3);

		$this->guzzler->expects($this->once())->post(static::getEndpointUri(Endpoint::LIST_FILE_VERSIONS));
		$this->guzzler->expects($this->exactly(3))->post(static::getEndpointUri(Endpoint::DELETE_FILE_VERSION));

		$response = $this->client->deleteAllFileVersions('fileId', null, null, null, 'bucketId');

		$this->assertInstanceOf(FileList::class, $response);

		$files = $response->getFilesArray();
		$this->assertCount(3, $files);
		$this->assertContainsOnlyInstancesOf(File::class, $files);
	}

	public function testDeletingNonExistentFileThrowsException()
	{
		$this->expectException(BadRequestException::class);

		$this->guzzler->queueResponse(
			MockResponse::fromFile('delete_file_non_existent.json', 400),
		);

		$this->assertNull($this->client->deleteFileVersion('fileId', 'fileName'));
	}

	public function testCopyFile()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('copy_file.json'),
		);

		$newFile = $this->client->copyFile(
			'fileId',
			'newFileName'
		);

		$this->assertInstanceOf(File::class, $newFile);
		$this->assertEquals('newFileName', $newFile->getName());
		$this->assertEquals('newFileId', $newFile->getId());
	}

	public function testHideFile()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('hide_file.json'),
		);

		$this->guzzler->expects($this->once())
			->post(static::getEndpointUri(Endpoint::AUTHORIZE_ACCOUNT))
			->post(static::getEndpointUri(Endpoint::HIDE_FILE));

		$file = $this->client->hideFile('testfile.bin', 'bucketId');

		$this->assertInstanceOf(File::class, $file);
	}
	
}
