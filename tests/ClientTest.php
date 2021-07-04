<?php

namespace Zaxbux\BackblazeB2\Tests;

use BlastCloud\Guzzler\UsesGuzzler;

use InvalidArgumentException;
use GuzzleHttp\Psr7\Stream;
use PHPUnit\Framework\TestCase;
use Zaxbux\BackblazeB2\Client;
use Zaxbux\BackblazeB2\B2\Object\Bucket;
use Zaxbux\BackblazeB2\B2\Object\File;
use Zaxbux\BackblazeB2\B2\Object\FileInfo;
use Zaxbux\BackblazeB2\B2\Response\FileListResponse;
use Zaxbux\BackblazeB2\B2\Type\BucketType;
use Zaxbux\BackblazeB2\Client\Exception\B2APIException;
use Zaxbux\BackblazeB2\Client\Exception\BadRequestException;
use Zaxbux\BackblazeB2\Client\Exception\DuplicateBucketNameException;
use Zaxbux\BackblazeB2\Client\Exception\NotFoundException;


class ClientTest extends TestCase
{
	use TestHelper;
	use UsesGuzzler;

	public function testCreatePublicBucket()
	{
		/*handler = $this->buildGuzzleHandlerFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'create_bucket_public.json')
		]);*/

		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'create_bucket_public.json')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		// Test that we get a public bucket back after creation
		$bucket = $client->createBucket(
			'Test bucket',
			BucketType::PUBLIC
		);
		$this->assertInstanceOf(Bucket::class, $bucket);
		$this->assertEquals('Test bucket', $bucket->getName());
		$this->assertEquals(BucketType::PUBLIC, $bucket->getType());
	}


	public function testCreatePrivateBucket()
	{
		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'create_bucket_private.json')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		// Test that we get a private bucket back after creation
		$bucket = $client->createBucket(
			'Test bucket',
			BucketType::PRIVATE
		);
		$this->assertInstanceOf(Bucket::class, $bucket);
		$this->assertEquals('Test bucket', $bucket->getName());
		$this->assertEquals(BucketType::PRIVATE, $bucket->getType());
	}

	public function testBucketAlreadyExistsExceptionThrown()
	{
		$this->expectException(DuplicateBucketNameException::class);

		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(400, [], 'create_bucket_exists.json')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);
		$client->createBucket(
			'I already exist',
			BucketType::PRIVATE
		);
	}

	public function testInvalidBucketTypeThrowsException()
	{
		$this->expectException(BadRequestException::class);

		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(400, [], 'create_bucket_invalid_type.json'),
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);
		$client->createBucket(
			'Test bucket',
			'i am not valid'
		);
	}

	public function testUpdateBucketToPrivate()
	{
		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'update_bucket_to_private.json')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		$bucket = $client->updateBucket(
			'bucketId',
			BucketType::PRIVATE
		);

		$this->assertInstanceOf(Bucket::class, $bucket);
		$this->assertEquals('bucketId', $bucket->getId());
		$this->assertEquals(BucketType::PRIVATE, $bucket->getType());
	}

	public function testUpdateBucketToPublic()
	{
		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'update_bucket_to_public.json')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		$bucket = $client->updateBucket(
			'bucketId',
			BucketType::PUBLIC
		);

		$this->assertInstanceOf(Bucket::class, $bucket);
		$this->assertEquals('bucketId', $bucket->getId());
		$this->assertEquals(BucketType::PUBLIC, $bucket->getType());
	}

	public function testList3Buckets()
	{
		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'list_buckets_3.json')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		$buckets = $client->listBuckets()->getBucketsArray();
		$this->assertIsArray($buckets);
		$this->assertCount(3, $buckets);
		$this->assertInstanceOf(Bucket::class, $buckets[0]);
	}

	public function testEmptyArrayWithNoBuckets()
	{
		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'list_buckets_0.json')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		$buckets = $client->listBuckets()->getBucketsArray();
		$this->assertIsArray($buckets);
		$this->assertCount(0, $buckets);
	}

	public function testDeleteBucket()
	{
		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'delete_bucket.json')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		$this->assertInstanceOf(Bucket::class, $client->deleteBucket(
			'bucketId'
		));
	}

	public function testBadJsonThrownDeletingNonExistentBucket()
	{
		$this->expectException(BadRequestException::class);

		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(400, [], 'delete_bucket_non_existent.json')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		$client->deleteBucket('bucketId');
	}

	public function testBucketNotEmptyThrownDeletingNonEmptyBucket()
	{
		$this->expectException(B2APIException::class);

		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(400, [], 'bucket_not_empty.json')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		$client->deleteBucket('bucketId');
	}

	public function testUploadingResource()
	{
		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'get_upload_url.json'),
			$this->buildResponseFromStub(200, [], 'upload.json')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		// Set up the resource being uploaded.
		$content = 'The quick brown box jumps over the lazy dog';
		$resource = fopen('php://memory', 'r+');
		fwrite($resource, $content);
		rewind($resource);

		$file = $client->uploadFile(
			$resource,
			'bucketId',
			'test.txt',
			null,
			[
				FileInfo::B2_FILE_INFO_MTIME => time() * 1000,
			]
		);

		$this->assertInstanceOf(File::class, $file);

		// We'll also check the Guzzle history to make sure the upload request got created correctly.
		$uploadRequest = $this->guzzler->getHistory(2, 'request');
		$this->assertEquals('uploadUrl', $uploadRequest->getRequestTarget());
		$this->assertEquals('authToken', $uploadRequest->getHeader('Authorization')[0]);
		$this->assertEquals(strlen($content), $uploadRequest->getHeader('Content-Length')[0]);
		$this->assertEquals('test.txt', $uploadRequest->getHeader('X-Bz-File-Name')[0]);
		$this->assertEquals(sha1($content), $uploadRequest->getHeader('X-Bz-Content-Sha1')[0]);
		$this->assertEqualsWithDelta((int) round(microtime(true) * 1000), (int) $uploadRequest->getHeader('X-Bz-Info-src_last_modified_millis')[0], 1000);
		$this->assertInstanceOf(Stream::class, $uploadRequest->getBody());
	}

	public function testUploadingString()
	{
		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'get_upload_url.json'),
			$this->buildResponseFromStub(200, [], 'upload.json')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		$content = 'The quick brown box jumps over the lazy dog';

		$file = $client->uploadFile(
			$content,
			'bucketId',
			'test.txt',
			null,
			[
				FileInfo::B2_FILE_INFO_MTIME => time() * 1000,
			]
		);

		$this->assertInstanceOf(File::class, $file);

		// We'll also check the Guzzle history to make sure the upload request got created correctly.
		$uploadRequest = $this->guzzler->getHistory(2, 'request');
		$this->assertEquals('uploadUrl', $uploadRequest->getRequestTarget());
		$this->assertEquals('authToken', $uploadRequest->getHeader('Authorization')[0]);
		$this->assertEquals(strlen($content), $uploadRequest->getHeader('Content-Length')[0]);
		$this->assertEquals('test.txt', $uploadRequest->getHeader('X-Bz-File-Name')[0]);
		$this->assertEquals(sha1($content), $uploadRequest->getHeader('X-Bz-Content-Sha1')[0]);
		$this->assertEqualsWithDelta((int) round(microtime(true) * 1000), (int) $uploadRequest->getHeader('X-Bz-Info-src_last_modified_millis')[0], 1000);
		$this->assertInstanceOf(Stream::class, $uploadRequest->getBody());
	}

	public function testUploadingWithCustomContentTypeAndLastModified()
	{
		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'get_upload_url.json'),
			$this->buildResponseFromStub(200, [], 'upload.json')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		$lastModified =  701568000000;
		$contentType = 'text/plain';

		$file = $client->uploadFile(
			'Test file content',
			'bucketId',
			'test.txt',
			$contentType,
			[
				'src_last_modified_millis' => $lastModified
			]
		);

		$this->assertInstanceOf(File::class, $file);

		// We'll also check the Guzzle history to make sure the upload request got created correctly.
		$uploadRequest = $this->guzzler->getHistory(2, 'request');
		$this->assertEquals($lastModified, $uploadRequest->getHeader('X-Bz-Info-src_last_modified_millis')[0]);
		$this->assertEquals($contentType, $uploadRequest->getHeader('Content-Type')[0]);
		$this->assertInstanceOf(Stream::class, $uploadRequest->getBody());
	}

	public function testDownloadByIdWithoutSavePath()
	{
		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'download_content')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		$fileContent = $client->downloadFileById('fileId')->getContents();

		$this->assertEquals($fileContent, 'The quick brown fox jumps over the lazy dog');
	}

	public function testDownloadByIdWithSavePath()
	{
		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'download_content')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		$client->downloadFileById('fileId', null, __DIR__ . '/test.txt');

		$this->assertFileExists(__DIR__ . '/test.txt');
		$this->assertEquals('The quick brown fox jumps over the lazy dog', file_get_contents(__DIR__ . '/test.txt'));

		unlink(__DIR__ . '/test.txt');
	}

	public function testDownloadingByIncorrectIdThrowsException()
	{
		$this->expectException(B2APIException::class);

		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(400, [], 'download_by_incorrect_id.json')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		$client->downloadFileById('incorrect');
	}

	public function testDownloadByPathWithoutSavePath()
	{
		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'download_content')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		$fileContent = $client->downloadFileByName('test.txt', 'test-bucket')->getContents();

		$this->assertEquals($fileContent, 'The quick brown fox jumps over the lazy dog');
	}

	public function testDownloadByPathWithSavePath()
	{
		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'download_content')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		$client->downloadFileByName('test.txt', 'test-bucket', null, __DIR__ . '/test.txt');

		$this->assertFileExists(__DIR__ . '/test.txt');
		$this->assertEquals('The quick brown fox jumps over the lazy dog', file_get_contents(__DIR__ . '/test.txt'));

		unlink(__DIR__ . '/test.txt');
	}

	public function testDownloadingByIncorrectPathThrowsException()
	{
		$this->expectException(NotFoundException::class);

		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(400, [], 'download_by_incorrect_path.json')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		$client->downloadFileByName('path/to/incorrect/file.txt', 'test-bucket');
	}

	public function testListFilesHandlesMultiplePages()
	{
		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'list_files_page1.json'),
			$this->buildResponseFromStub(200, [], 'list_files_page2.json')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		$files = $client->listAllFileNames('bucketId');

		$this->assertIsIterable($files);
		$this->assertInstanceOf(File::class, $files->current());
		$this->assertCount(1500, $files);
	}

	public function testListFilesReturnsEmptyArrayWithNoFiles()
	{
		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'list_files_empty.json')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		$response = $client->listFileNames('bucketId');
		$this->assertInstanceOf(FileListResponse::class, $response);
		$files = $response->getFilesArray();
		$this->assertCount(0, $files);
	}

	public function testGetFile()
	{
		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'get_file.json')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		$file = $client->getFileById('bucketId', 'fileId');

		$this->assertInstanceOf(File::class, $file);
	}

	public function testGettingNonExistentFileThrowsException()
	{
		$this->expectException(BadRequestException::class);

		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(400, [], 'get_file_non_existent.json')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		$client->getFileById('bucketId', 'fileId');
	}

	public function testDeleteFile()
	{
		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'get_file.json'),
			$this->buildResponseFromStub(200, [], 'delete_file.json')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		$fileId = $client->getFileByName('bucketId', 'Test file.bin')->getId();

		$this->assertInstanceOf(File::class, $client->deleteFileVersion('Test file.bin', $fileId));
	}

	public function testDeleteFileRetrievesFileNameWhenNotProvided()
	{
		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'get_file.json'),
			$this->buildResponseFromStub(200, [], 'delete_file.json')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		$this->assertNull($client->deleteAllFileVersions('bucketId', 'fileId'));
	}

	public function testDeletingNonExistentFileThrowsException()
	{
		$this->expectException(BadRequestException::class);

		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(400, [], 'delete_file_non_existent.json')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		$this->assertNull($client->deleteFileVersion('fileId', 'fileName'));
	}

	public function testCopyFile()
	{
		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'copy_file.json')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		$newFile = $client->copyFile(
			'fileId',
			'newFileName'
		);

		$this->assertInstanceOf(File::class, $newFile);
		$this->assertEquals('newFileName', $newFile->getName());
		$this->assertEquals('newFileId', $newFile->getId());
	}

	public function testCopyPart()
	{
		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'copy_part.json')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		$newFilePart = $client->copyPart(
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
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'cancel_large_file.json')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		$file = $client->cancelLargeFile(
			'largeFileId'
		);

		$this->assertInstanceOf(File::class, $file);
		$this->assertEquals('largeFileId', $file->getId());
	}

	public function testListUnfinishedLargeFiles()
	{
		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'list_unfinished_large_files.json')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		$response = $client->listUnfinishedLargeFiles('bucketId');
		$this->assertInstanceOf(FileListResponse::class, $response);

		$files = $response->getFilesArray();
		$this->assertInstanceOf(File::class, $files[0]);
	}

	public function testHideFile()
	{
		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'hide_file.json')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		$this->assertInstanceOf(File::class, $client->hideFile('bucketId', 'testfile.bin'));
	}

	public function testGetDownloadAuthorization()
	{
		$this->guzzler->queueResponse(
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'get_download_authorization.json')
		);

		$client = new Client('testId', 'testKey', null, [
			'handler' => $this->guzzler->getHandlerStack(),
		]);

		$authorization = $client->getDownloadAuthorization(
			'bucketId',
			'public',
			60
		);

		$this->assertEquals('downloadAuthToken', $authorization->getAuthorizationToken());
	}
}
