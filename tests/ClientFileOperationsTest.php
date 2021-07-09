<?php

namespace tests;

use Zaxbux\BackblazeB2\Response\FileList;
use Zaxbux\BackblazeB2\Object\File;
use Zaxbux\BackblazeB2\Exceptions\Request\BadRequestException;
use Zaxbux\BackblazeB2\Http\Endpoint;
use Zaxbux\BackblazeB2\Object\File\FileLock;

class ClientFileOperationsTest extends ClientTestBase
{
	public function testCopyFile()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('copy_file.json'),
		);

		$newFile = $this->client->copyFile(
			'fileId',
			'newFileName'
		);

		static::assertInstanceOf(File::class, $newFile);
		static::assertEquals('newFileName', $newFile->name());
		static::assertEquals('newFileId', $newFile->id());
	}

	public function testDeleteFileVersion()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('get_file.json'),
			MockResponse::fromFile('delete_file.json'),
		);

		$fileId = $this->client->getFileByName('Test file.bin', 'bucketId')->id();

		static::assertInstanceOf(File::class, $this->client->deleteFileVersion($fileId, 'Test file.bin'));
	}
	

	public function testDeleteFileWithoutName()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('get_file_info.json'),
			MockResponse::fromFile('delete_file.json'),
		);

		$this->guzzler->expects($this->once())
			->post(static::getEndpointUri(Endpoint::GET_FILE_INFO))
			->post(static::getEndpointUri(Endpoint::DELETE_FILE_VERSION));

		$file = $this->client->deleteFileVersion('fileId');

		static::assertInstanceOf(File::class, $file);
	}

	public function testDeletingNonExistentFileThrowsException()
	{
		$this->expectException(BadRequestException::class);

		$this->guzzler->queueResponse(
			MockResponse::fromFile('delete_file_non_existent.json', 400),
		);

		static::assertNull($this->client->deleteFileVersion('fileId', 'fileName'));
	}

	public function testGetFileInfo()
	{
		$this->guzzler->expects($this->once())
			->post(static::getEndpointUri(Endpoint::GET_FILE_INFO))
			->withJson(['fileId' => 'file_id']);
		
		$this->guzzler->queueResponse(MockResponse::fromFile('get_file_info.json'));

		$file = $this->client->getFileInfo('file_id');

		static::assertInstanceOf(File::class, $file);
	}

	public function testHideFile()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('hide_file.json'),
		);

		$this->guzzler->expects($this->once())
			->post(static::getEndpointUri(Endpoint::HIDE_FILE));

		$file = $this->client->hideFile('testfile.bin', 'bucketId');

		static::assertInstanceOf(File::class, $file);
	}

	public function testListFileNames()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('list_file_versions.json'),
		);

		$this->guzzler->expects($this->once())
			->post(static::getEndpointUri(Endpoint::LIST_FILE_NAMES));

		$response = $this->client->listFileNames('bucketId');

		static::assertInstanceOf(FileList::class, $response);
		static::assertCount(3, $response);
		static::assertEquals(null, $response->nextFileId());
		static::assertEquals(null, $response->nextFileName());
	}

	public function testListFileVersions()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('list_file_versions.json'),
		);

		$this->guzzler->expects($this->once())
			->post(static::getEndpointUri(Endpoint::LIST_FILE_VERSIONS));

		$response = $this->client->listFileVersions('bucketId');

		static::assertInstanceOf(FileList::class, $response);
		static::assertCount(3, $response);
		static::assertEquals(null, $response->nextFileId());
		static::assertEquals(null, $response->nextFileName());
	}

	public function testUpdateLegalFileHold()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('update_file_legal_hold.json'),
		);

		$this->guzzler->expects($this->once())
			->post(static::getEndpointUri(Endpoint::UPDATE_FILE_LEGAL_HOLD));

		$file = $this->client->updateFileLegalHold('file_id', 'file_name', FileLock::LEGAL_HOLD_ENABLED);

		static::assertInstanceOf(File::class, $file);
	}

	public function testUpdateLegalFileHoldWithoutFileName()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('get_file_info.json'),
			MockResponse::fromFile('update_file_legal_hold.json'),
		);

		$this->guzzler->expects($this->once())
			->post(static::getEndpointUri(Endpoint::GET_FILE_INFO))
			->post(static::getEndpointUri(Endpoint::UPDATE_FILE_LEGAL_HOLD));

		$file = $this->client->updateFileLegalHold('file_id', null, FileLock::LEGAL_HOLD_ENABLED);

		static::assertInstanceOf(File::class, $file);
	}

	public function testUpdateFileRetention()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('update_file_retention.json'),
		);

		$this->guzzler->expects($this->once())
			->post(static::getEndpointUri(Endpoint::UPDATE_FILE_RETENTION));

		$file = $this->client->updateFileRetention('file_id', 'file_name', [
			'mode' => '',
			'remainUntilTimestamp' => 0
		]);

		static::assertInstanceOf(File::class, $file);
	}

	public function testUpdateFileRetentionWithoutFileName()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('get_file_info.json'),
			MockResponse::fromFile('update_file_retention.json'),
		);

		$this->guzzler->expects($this->once())
			->post(static::getEndpointUri(Endpoint::GET_FILE_INFO))
			->post(static::getEndpointUri(Endpoint::UPDATE_FILE_RETENTION));

		$file = $this->client->updateFileRetention('file_id', null, [
			'mode' => '',
			'remainUntilTimestamp' => 0
		]);

		static::assertInstanceOf(File::class, $file);
	}
	

	public function testListAllFileNames()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('list_files_page1.json'),
			MockResponse::fromFile('list_files_page2.json'),
		);

		$files = $this->client->listAllFileNames('bucketId');

		static::assertIsIterable($files);
		static::assertInstanceOf(File::class, $files->current());
		static::assertCount(1500, $files);
	}

	public function testListFileNamesWithNoFiles()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('list_files_empty.json'),
		);

		$response = $this->client->listFileNames('bucketId');
		static::assertInstanceOf(FileList::class, $response);
		// /$files = $response->getFilesArray();
		static::assertCount(0, $response);
	}

	public function testListAllFileVersions()
	{

	}

	public function testGetFileById()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('get_file.json'),
		);

		$file = $this->client->getFileInfo('fileId');

		static::assertInstanceOf(File::class, $file);
	}

	public function testGettingNonExistentFileThrowsException()
	{
		$this->expectException(BadRequestException::class);

		$this->guzzler->queueResponse(
			MockResponse::fromFile('get_file_non_existent.json', 400),
		);

		$this->client->getFileInfo('fileId');
	}

	public function testDeleteAllFileVersions()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('list_file_versions.json'),
		);
		$this->guzzler->queueMany(MockResponse::fromFile('delete_file.json'), 3);

		$this->guzzler->expects($this->once())->post(static::getEndpointUri(Endpoint::LIST_FILE_VERSIONS));
		$this->guzzler->expects($this->exactly(3))->post(static::getEndpointUri(Endpoint::DELETE_FILE_VERSION));

		$response = $this->client->deleteAllFileVersions('fileId', null, null, null, 'bucketId');

		static::assertInstanceOf(FileList::class, $response);

		//$files = $response->getFilesArray();
		static::assertCount(3, $response);
		static::assertContainsOnlyInstancesOf(File::class, $response);
	}
	
}
