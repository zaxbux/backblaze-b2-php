<?php

namespace tests;

use Zaxbux\BackblazeB2\Response\FileList;
use Zaxbux\BackblazeB2\Object\File;

class ClientLargeFileOperationsTest extends ClientTestBase
{
	public function testCopyPart()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('copy_part.json'),
		);

		$newFilePart = $this->client->copyPart(
			'fileId',
			'largeFileId',
			1
		);

		$this->assertInstanceOf(File::class, $newFilePart);
		$this->assertEquals(1, $newFilePart->getPartNumber());
		$this->assertEquals('largeFileId', $newFilePart->getId());
	}

	public function testCancelLargeFile()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('cancel_large_file.json'),
		);

		$file = $this->client->cancelLargeFile(
			'largeFileId'
		);

		$this->assertInstanceOf(File::class, $file);
		$this->assertEquals('largeFileId', $file->getId());
	}

	public function testListUnfinishedLargeFiles()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('list_unfinished_large_files.json'),
		);

		$response = $this->client->listUnfinishedLargeFiles('bucketId');
		$this->assertInstanceOf(FileList::class, $response);

		$files = $response->getFilesArray();
		$this->assertInstanceOf(File::class, $files[0]);
	}

}
