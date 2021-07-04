<?php

namespace Zaxbux\BackblazeB2\Tests;

use InvalidArgumentException;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Stream;
use PHPUnit\Framework\TestCase;
use Zaxbux\BackblazeB2\Client;
use Zaxbux\BackblazeB2\B2\Object\Bucket;
use Zaxbux\BackblazeB2\B2\Object\File;
use Zaxbux\BackblazeB2\B2\Type\BucketType;
use Zaxbux\BackblazeB2\Client\Exception\BadJsonException;
use Zaxbux\BackblazeB2\Client\Exception\BadValueException;
use Zaxbux\BackblazeB2\Client\Exception\BucketAlreadyExistsException;
use Zaxbux\BackblazeB2\Client\Exception\BucketNotEmptyException;
use Zaxbux\BackblazeB2\Client\Exception\NotFoundException;


class ClientTest extends TestCase
{
	use TestHelper;

	public function testCreatePublicBucket() {
		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'create_bucket_public.json')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);

		// Test that we get a public bucket back after creation
		$bucket = $client->createBucket(
			'Test bucket',
			BucketType::PUBLIC
		);
		$this->assertInstanceOf(Bucket::class, $bucket);
		$this->assertEquals('Test bucket', $bucket->getName());
		$this->assertEquals(BucketType::PUBLIC, $bucket->getType());
	}

	public function testCreatePrivateBucket() {
		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'create_bucket_private.json')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);

		// Test that we get a private bucket back after creation
		$bucket = $client->createBucket(
			'Test bucket',
			BucketType::PRIVATE
		);
		$this->assertInstanceOf(Bucket::class, $bucket);
		$this->assertEquals('Test bucket', $bucket->getName());
		$this->assertEquals(BucketType::PRIVATE, $bucket->getType());
	}

	public function testBucketAlreadyExistsExceptionThrown() {
		$this->expectException(BucketAlreadyExistsException::class);

		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(400, [], 'create_bucket_exists.json')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);
		$client->createBucket(
			'I already exist',
			BucketType::PRIVATE
		);
	}

	public function testInvalidBucketTypeThrowsException() {
		$this->expectException(InvalidArgumentException::class);

		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);
		$client->createBucket(
			'Test bucket',
			'i am not valid'
		);
	}

	public function testUpdateBucketToPrivate() {
		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'update_bucket_to_private.json')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);

		$bucket = $client->updateBucket(
			'bucketId',
			BucketType::PRIVATE
		);

		$this->assertInstanceOf(Bucket::class, $bucket);
		$this->assertEquals('bucketId', $bucket->getId());
		$this->assertEquals(BucketType::PRIVATE, $bucket->getType());
	}

	public function testUpdateBucketToPublic() {
		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'update_bucket_to_public.json')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);

		$bucket = $client->updateBucket(
			'bucketId',
			BucketType::PUBLIC
		);

		$this->assertInstanceOf(Bucket::class, $bucket);
		$this->assertEquals('bucketId', $bucket->getId());
		$this->assertEquals(BucketType::PUBLIC, $bucket->getType());
	}

	public function testList3Buckets() {
		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'list_buckets_3.json')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);

		$buckets = $client->listBuckets();
		//$this->assertInternalType('array', $buckets);
		$this->assertCount(3, $buckets);
		$this->assertInstanceOf(Bucket::class, $buckets[0]);
	}

	public function testEmptyArrayWithNoBuckets() {
		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'list_buckets_0.json')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);

		$buckets = $client->listBuckets();
		//$this->assertInternalType('array', $buckets);
		$this->assertCount(0, $buckets);
	}

	public function testDeleteBucket() {
		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'delete_bucket.json')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);

		$this->assertInstanceOf(Bucket::class, $client->deleteBucket(
			'bucketId'
		));
	}

	public function testBadJsonThrownDeletingNonExistentBucket() {
		$this->expectException(BadJsonException::class);

		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(400, [], 'delete_bucket_non_existent.json')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);

		$client->deleteBucket('bucketId');
	}

	public function testBucketNotEmptyThrownDeletingNonEmptyBucket() {
		$this->expectException(BucketNotEmptyException::class);

		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(400, [], 'bucket_not_empty.json')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);

		$client->deleteBucket('bucketId');
	}

	public function testUploadingResource() {
		$container = [];
		$history = Middleware::history($container);
		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'get_upload_url.json'),
			$this->buildResponseFromStub(200, [], 'upload.json')
		], $history);

		$client = new Client('testId', 'testKey', null, $guzzle);

		// Set up the resource being uploaded.
		$content = 'The quick brown box jumps over the lazy dog';
		$resource = fopen('php://memory', 'r+');
		fwrite($resource, $content);
		rewind($resource);

		$file = $client->uploadFile(
			$resource,
			'bucketId',
			'test.txt',
		);

		$this->assertInstanceOf(File::class, $file);

		// We'll also check the Guzzle history to make sure the upload request got created correctly.
		$uploadRequest = $container[2]['request'];
		$this->assertEquals('uploadUrl', $uploadRequest->getRequestTarget());
		$this->assertEquals('authToken', $uploadRequest->getHeader('Authorization')[0]);
		$this->assertEquals(strlen($content), $uploadRequest->getHeader('Content-Length')[0]);
		$this->assertEquals('test.txt', $uploadRequest->getHeader('X-Bz-File-Name')[0]);
		$this->assertEquals(sha1($content), $uploadRequest->getHeader('X-Bz-Content-Sha1')[0]);
		$this->assertEquals(round(microtime(true) * 1000), $uploadRequest->getHeader('X-Bz-Info-src_last_modified_millis')[0], '', 100);
		$this->assertInstanceOf(Stream::class, $uploadRequest->getBody());
	}

	public function testUploadingString() {
		$container = [];
		$history = Middleware::history($container);
		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'get_upload_url.json'),
			$this->buildResponseFromStub(200, [], 'upload.json')
		], $history);

		$client = new Client('testId', 'testKey', null, $guzzle);

		$content = 'The quick brown box jumps over the lazy dog';

		$file = $client->uploadFile(
			$content,
			'bucketId',
			'test.txt'
		);

		$this->assertInstanceOf(File::class, $file);

		// We'll also check the Guzzle history to make sure the upload request got created correctly.
		$uploadRequest = $container[2]['request'];
		$this->assertEquals('uploadUrl', $uploadRequest->getRequestTarget());
		$this->assertEquals('authToken', $uploadRequest->getHeader('Authorization')[0]);
		$this->assertEquals(strlen($content), $uploadRequest->getHeader('Content-Length')[0]);
		$this->assertEquals('test.txt', $uploadRequest->getHeader('X-Bz-File-Name')[0]);
		$this->assertEquals(sha1($content), $uploadRequest->getHeader('X-Bz-Content-Sha1')[0]);
		$this->assertEquals(round(microtime(true) * 1000), $uploadRequest->getHeader('X-Bz-Info-src_last_modified_millis')[0], '', 100);
		$this->assertInstanceOf(Stream::class, $uploadRequest->getBody());
	}

	public function testUploadingWithCustomContentTypeAndLastModified() {
		$container = [];
		$history = Middleware::history($container);
		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'get_upload_url.json'),
			$this->buildResponseFromStub(200, [], 'upload.json')
		], $history);

		$client = new Client('testId', 'testKey', null, $guzzle);

		// My birthday :)
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
		$uploadRequest = $container[2]['request'];
		$this->assertEquals($lastModified, $uploadRequest->getHeader('X-Bz-Info-src_last_modified_millis')[0]);
		$this->assertEquals($contentType, $uploadRequest->getHeader('Content-Type')[0]);
		$this->assertInstanceOf(Stream::class, $uploadRequest->getBody());
	}

	public function testDownloadByIdWithoutSavePath() {
		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'download_content')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);

		$fileContent = $client->downloadFileById('fileId');

		$this->assertEquals($fileContent, 'The quick brown fox jumps over the lazy dog');
	}

	public function testDownloadByIdWithSavePath() {
		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'download_content')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);

		$client->downloadFileById('fileId', null, null, __DIR__ . '/test.txt');

		$this->assertFileExists(__DIR__.'/test.txt');
		$this->assertEquals('The quick brown fox jumps over the lazy dog', file_get_contents(__DIR__.'/test.txt'));

		unlink(__DIR__.'/test.txt');
	}

	public function testDownloadingByIncorrectIdThrowsException() {
		$this->expectException(BadValueException::class);

		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(400, [], 'download_by_incorrect_id.json')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);

		$client->downloadFileById('incorrect');
	}

	public function testDownloadByPathWithoutSavePath() {
		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'download_content')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);

		$fileContent = $client->downloadFileByName('test.txt', 'test-bucket');

		$this->assertEquals($fileContent, 'The quick brown fox jumps over the lazy dog');
	}

	public function testDownloadByPathWithSavePath() {
		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'download_content')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);

		$client->downloadFileByName('test.txt', 'test-bucket', null, null, __DIR__.'/test.txt');

		$this->assertFileExists(__DIR__.'/test.txt');
		$this->assertEquals('The quick brown fox jumps over the lazy dog', file_get_contents(__DIR__.'/test.txt'));

		unlink(__DIR__.'/test.txt');
	}

	public function testDownloadingByIncorrectPathThrowsException() {
		$this->expectException(NotFoundException::class);

		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(400, [], 'download_by_incorrect_path.json')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);

		$client->downloadFileByName('path/to/incorrect/file.txt', 'test-bucket');
	}

	public function testListFilesHandlesMultiplePages() {
		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'list_files_page1.json'),
			$this->buildResponseFromStub(200, [], 'list_files_page2.json')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);

		$files = $client->listFileNames('bucketId');

		//$this->assertInternalType('array', $files);
		$this->assertInstanceOf(File::class, $files[0]);
		$this->assertCount(1500, $files);
	}

	public function testListFilesReturnsEmptyArrayWithNoFiles() {
		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'list_files_empty.json')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);

		$files = $client->listFileNames('bucketId');

		//$this->assertInternalType('array', $files);
		$this->assertCount(0, $files);
	}

	public function testGetFile() {
		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'get_file.json')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);

		$file = $client->getFileById('bucketId', 'fileId');

		$this->assertInstanceOf(File::class, $file);
	}

	public function testGettingNonExistentFileThrowsException() {
		$this->expectException(BadJsonException::class);

		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(400, [], 'get_file_non_existent.json')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);

		$client->getFileById('bucketId', 'fileId');
	}

	public function testDeleteFile() {
		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'get_file.json'),
			$this->buildResponseFromStub(200, [], 'delete_file.json')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);

		$fileId = $client->getFileByName('bucketId', 'Test file.bin')->getId();

		$this->assertTrue($client->deleteFileVersion('Test file.bin', $fileId));
	}

	public function testDeleteFileRetrievesFileNameWhenNotProvided() {
		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'get_file.json'),
			$this->buildResponseFromStub(200, [], 'delete_file.json')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);

		$this->assertTrue($client->deleteAllFileVersions('bucketId', 'fileId'));
	}

	public function testDeletingNonExistentFileThrowsException() {
		$this->expectException(BadJsonException::class);

		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(400, [], 'delete_file_non_existent.json')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);

		$this->assertTrue($client->deleteFileVersion('fileId','fileName'));
	}

	public function testCopyFile() {
		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'copy_file.json')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);

		$newFile = $client->copyFile(
			'fileId',
			'newFileName'
		);

		$this->assertInstanceOf(File::class, $newFile);
		$this->assertEquals('newFileName', $newFile->getName());
		$this->assertEquals('newFileId', $newFile->getId());
	}

	public function testCopyPart() {
		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'copy_part.json')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);

		$newFilePart = $client->copyPart(
			'fileId',
			'largeFileId',
			1
		);

		//$this->assertInternalType('array', $newFilePart);
		$this->assertEquals(1, $newFilePart['partNumber']);
		$this->assertInstanceOf(File::class, $newFilePart['file']);
		$this->assertEquals('largeFileId', $newFilePart['file']->getId());
	}

	public function testCancelLargeFile() {
		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'cancel_large_file.json')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);

		$response = $client->cancelLargeFile(
			'largeFileId'
		);

		//$this->assertInternalType('array', $response);
		$this->assertEquals('largeFileId', $response['fileId']);
	}

	public function testListUnfinishedLargeFiles() {
		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'list_unfinished_large_files.json')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);

		$response = $client->listUnfinishedLargeFiles('bucketId');

		//$this->assertInternalType('array', $response);
		$this->assertInstanceOf(File::class, $response['files'][0]);
	}

	public function testHideFile() {
		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'hide_file.json')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);

		$this->assertTrue($client->hideFile('bucketId', 'testfile.bin'));
	}

	public function testGetDownloadAuthorization() {
		$guzzle = $this->buildGuzzleFromResponses([
			$this->buildResponseFromStub(200, [], 'authorize_account.json'),
			$this->buildResponseFromStub(200, [], 'get_download_authorization.json')
		]);

		$client = new Client('testId', 'testKey', null, $guzzle);

		$response = $client->getDownloadAuthorization(
			'bucketId',
			'public',
			60
		);

		$this->assertEquals('downloadAuthToken', $response);
	}
}
