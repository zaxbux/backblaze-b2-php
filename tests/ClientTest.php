<?php

namespace tests;

use BlastCloud\Guzzler\UsesGuzzler;
use GuzzleHttp\Psr7\Stream;
use PHPUnit\Framework\TestCase;
use Zaxbux\BackblazeB2\Response\FileList;
use Zaxbux\BackblazeB2\Client;
use Zaxbux\BackblazeB2\Object\Bucket;
use Zaxbux\BackblazeB2\Object\File;
use Zaxbux\BackblazeB2\Object\File\FileInfo;
use Zaxbux\BackblazeB2\Object\Bucket\BucketType;
use Zaxbux\BackblazeB2\Exceptions\B2APIException;
use Zaxbux\BackblazeB2\Exceptions\BadRequestException;
use Zaxbux\BackblazeB2\Exceptions\DuplicateBucketNameException;
use Zaxbux\BackblazeB2\Exceptions\NotFoundException;


class ClientTest extends TestCase
{
	use UsesGuzzler;

	/** @var Client */
	private $client;

	protected function setUp(): void
	{
		parent::setUp();

		$this->client = new Client([
			'applicationKeyId' => 'testId',
			'applicationKey'   => 'testKey',
			'handler'          => $this->guzzler->getHandlerStack()
			//'maxRetries'       => 0,
		]);

		$this->guzzler->queueResponse(
			MockResponse::fromFile('authorize_account.json'),
		);

		$this->guzzler->expects($this->once())->get(Client::B2_API_BASE_URL . Client::B2_API_V2 . Endpoint::AUTHORIZE_ACCOUNT);
	}

	public function testCreateBucket()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('create_bucket_private.json'),
		);

		$this->guzzler->expects($this->once())
			->post(Endpoint::CREATE_BUCKET);

		$bucket = $this->client->createBucket(
			'Test bucket',
		);

		// Test that we get a private bucket back after creation
		$this->assertInstanceOf(Bucket::class, $bucket);
		$this->assertEquals('Test bucket', $bucket->getName());
		$this->assertEquals(BucketType::PRIVATE, $bucket->getType());
	}

	public function testCreatePublicBucket()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('create_bucket_public.json'),
		);

		$this->guzzler->expects($this->once())
			->post(Endpoint::CREATE_BUCKET);

		// Test that we get a public bucket back after creation
		$bucket = $this->client->createBucket(
			'Test bucket',
			BucketType::PUBLIC
		);

		$this->assertInstanceOf(Bucket::class, $bucket);
		$this->assertEquals('Test bucket', $bucket->getName());
		$this->assertEquals(BucketType::PUBLIC, $bucket->getType());
	}

	public function testBucketAlreadyExistsExceptionThrown()
	{
		$this->expectException(DuplicateBucketNameException::class);

		$this->guzzler->queueResponse(
			MockResponse::fromFile('create_bucket_exists.json', 400),
		);
		$this->client->createBucket(
			'I already exist',
			BucketType::PRIVATE
		);
	}

	public function testInvalidBucketTypeThrowsException()
	{
		$this->expectException(BadRequestException::class);

		$this->guzzler->queueResponse(
			MockResponse::fromFile('create_bucket_invalid_type.json', 400),
		);
		$this->client->createBucket(
			'Test bucket',
			'i am not valid'
		);
	}

	public function testUpdateBucketToPrivate()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('update_bucket_to_private.json'),
		);

		$bucket = $this->client->updateBucket(
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
			MockResponse::fromFile('update_bucket_to_public.json'),
		);

		$bucket = $this->client->updateBucket(
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
			MockResponse::fromFile('list_buckets_3.json'),
		);

		$buckets = $this->client->listBuckets()->getBucketsArray();
		$this->assertIsArray($buckets);
		$this->assertCount(3, $buckets);
		$this->assertInstanceOf(Bucket::class, $buckets[0]);
	}

	public function testEmptyArrayWithNoBuckets()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('list_buckets_0.json'),
		);

		$buckets = $this->client->listBuckets()->getBucketsArray();
		$this->assertIsArray($buckets);
		$this->assertCount(0, $buckets);
	}

	public function testDeleteBucket()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('delete_bucket.json'),
		);

		$this->guzzler->expects($this->once())
			->post('/b2_delete_bucket');

		$this->assertInstanceOf(Bucket::class, $this->client->deleteBucket(
			'bucketId'
		));
	}

	public function testBadJsonThrownDeletingNonExistentBucket()
	{
		$this->expectException(BadRequestException::class);

		$this->guzzler->queueResponse(
			MockResponse::fromFile('delete_bucket_non_existent.json', 400),
		);

		$this->client->deleteBucket('bucketId');
	}

	public function testBucketNotEmptyThrownDeletingNonEmptyBucket()
	{
		$this->expectException(B2APIException::class);

		$this->guzzler->queueResponse(
			MockResponse::fromFile('bucket_not_empty.json', 400),
		);

		$this->client->deleteBucket('bucketId');
	}
	

	/*public function testUploadingFile() {
		
	}*/

	
	public function testUploadingResource()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('get_upload_url.json'),
			MockResponse::fromFile('upload.json'),
		);

		$this->guzzler->expects($this->once())->post('/b2_get_upload_url');

		// Set up the resource being uploaded.
		$content = 'The quick brown box jumps over the lazy dog';
		$resource = fopen('php://memory', 'r+');
		$mtime = time() * 1000;
		fwrite($resource, $content);
		rewind($resource);

		$file = $this->client->uploadFile('bucketId', 'test.txt', $resource, null, [
			FileInfo::B2_FILE_INFO_MTIME => $mtime,
		]);

		$this->assertInstanceOf(File::class, $file);

		$this->guzzler->expects($this->once())
			->withEndpoint('uploadUrl', 'POST')
			->withHeader('Authorization', 'authToken')
			->withHeader('Content-Length', strlen($content))
			->withHeader(File::HEADER_X_BZ_FILE_NAME, 'test.txt')
			->withHeader(File::HEADER_X_BZ_CONTENT_SHA1, sha1($content))
			->withHeader(FileInfo::HEADER_PREFIX.FileInfo::B2_FILE_INFO_MTIME, $mtime)
			->withBody($content);
	}

	public function testUploadingString()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('get_upload_url.json'),
			MockResponse::fromFile('upload.json'),
		);

		$content = 'The quick brown box jumps over the lazy dog';
		$mtime = time() * 1000;

		$file = $this->client->uploadFile(
			'bucketId',
			'test.txt',
			$content,
			null,
			[
				FileInfo::B2_FILE_INFO_MTIME => $mtime,
			]
		);

		$this->assertInstanceOf(File::class, $file);

		$this->guzzler->expects($this->once())
			->withEndpoint('uploadUrl', 'POST')
			->withHeader('Authorization', 'authToken')
			->withHeader('Content-Length', strlen($content))
			->withHeader(File::HEADER_X_BZ_FILE_NAME, 'test.txt')
			->withHeader(File::HEADER_X_BZ_CONTENT_SHA1, sha1($content))
			->withHeader(FileInfo::HEADER_PREFIX.FileInfo::B2_FILE_INFO_MTIME, $mtime)
			->withBody($content);
	}

	public function testUploadingWithCustomContentTypeAndLastModified()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('get_upload_url.json'),
			MockResponse::fromFile('upload.json'),
		);

		$lastModified =  701568000000;
		$contentType = 'text/plain';

		$file = $this->client->uploadFile(
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

		$file = $this->client->getFileById('bucketId', 'fileId');

		$this->assertInstanceOf(File::class, $file);
	}

	public function testGettingNonExistentFileThrowsException()
	{
		$this->expectException(BadRequestException::class);

		$this->guzzler->queueResponse(
			MockResponse::fromFile('get_file_non_existent.json', 400),
		);

		$this->client->getFileById('bucketId', 'fileId');
	}

	public function testDeleteFile()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('get_file.json'),
			MockResponse::fromFile('delete_file.json'),
		);

		$fileId = $this->client->getFileByName('bucketId', 'Test file.bin')->getId();

		$this->assertInstanceOf(File::class, $this->client->deleteFileVersion('Test file.bin', $fileId));
	}
	

	public function testDeleteFileRetrievesFileNameWhenNotProvided()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('list_file_versions.json'),
		);
		$this->guzzler->queueMany(MockResponse::fromFile('delete_file.json'), 3);

		$this->guzzler->expects($this->once())->post(Endpoint::LIST_FILE_VERSIONS);
		$this->guzzler->expects($this->exactly(3))->post(Endpoint::DELETE_FILE_VERSION);

		$response = $this->client->deleteAllFileVersions('bucketId', null, null, 'fileId');

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

	public function testHideFile()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('hide_file.json'),
		);

		$this->guzzler->expects($this->once())
			->post(Endpoint::AUTHORIZE_ACCOUNT)
			->post(Endpoint::HIDE_FILE);

		$file = $this->client->hideFile('bucketId', 'testfile.bin');

		$this->assertInstanceOf(File::class, $file);
	}

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
	
}
