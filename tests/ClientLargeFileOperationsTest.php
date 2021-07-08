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

		static::assertInstanceOf(File::class, $newFilePart);
		static::assertEquals(1, $newFilePart->getPartNumber());
		static::assertEquals('largeFileId', $newFilePart->getId());
	}

	public function testCancelLargeFile()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('cancel_large_file.json'),
		);

		$file = $this->client->cancelLargeFile(
			'largeFileId'
		);

		static::assertInstanceOf(File::class, $file);
		static::assertEquals('largeFileId', $file->getId());
	}

	public function testListUnfinishedLargeFiles()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('list_unfinished_large_files.json'),
		);

		$response = $this->client->listUnfinishedLargeFiles('bucketId');
		static::assertInstanceOf(FileList::class, $response);

		//$files = $response->getFilesArray();
		static::assertInstanceOf(File::class, $response->current());
	}

}
