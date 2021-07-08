<?php

namespace tests;

use Zaxbux\BackblazeB2\Utils as ClientUtils;
use Zaxbux\BackblazeB2\Helpers\UploadHelper;
use Zaxbux\BackblazeB2\Http\Endpoint;
use Zaxbux\BackblazeB2\Object\File;
use Zaxbux\BackblazeB2\Object\File\FileInfo;

class ClientUploadTest extends ClientTestBase
{
	public function testUploadingFile() {
		$this->guzzler->queueResponse(
			MockResponse::fromFile('get_upload_url.json'),
			MockResponse::fromFile('upload.json'),
		);

		$filePath = ClientUtils::joinFilePaths(__DIR__, 'responses', 'download_content');

		$file = UploadHelper::instance($this->client)->uploadFile(
			'/file/name.txt',
			'bucketId',
			$filePath,
			'text/plain'
		);

		static::assertInstanceOf(File::class, $file);

		$this->guzzler->expects($this->once())
			->post('https://pod-000-1005-03.backblaze.com/b2api/v2/b2_upload_file?cvt=c001_v0001005_t0027&bucket=4a48fe8875c6214145260818')
			->withHeader('Content-Length', 43)
			->withHeader(File::HEADER_X_BZ_FILE_NAME, '/file/name.txt')
			->withHeader(File::HEADER_X_BZ_CONTENT_SHA1, sha1_file($filePath))
			->withBody(file_get_contents($filePath));
	}
	
	public function testUploadingResource()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('get_upload_url.json'),
			MockResponse::fromFile('upload.json'),
		);

		// Set up the resource being uploaded.
		$content = 'The quick brown box jumps over the lazy dog';
		$resource = fopen('php://memory', 'r+');
		fwrite($resource, $content);
		rewind($resource);

		$file = $this->client->uploadFile(
			'test.txt',
			'bucketId',
			$resource
		);

		static::assertInstanceOf(File::class, $file);

		$this->guzzler->expects($this->once())->post(static::getEndpointUri(Endpoint::GET_UPLOAD_URL));
		$this->guzzler->expects($this->once())
			->post('https://pod-000-1005-03.backblaze.com/b2api/v2/b2_upload_file?cvt=c001_v0001005_t0027&bucket=4a48fe8875c6214145260818')
			->withHeader('Authorization', 'authToken')
			->withHeader('Content-Length', strlen($content))
			->withHeader(File::HEADER_X_BZ_FILE_NAME, 'test.txt')
			->withHeader(File::HEADER_X_BZ_CONTENT_SHA1, sha1($content))
			->withBody($content);
	}

	public function testUploadingString()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('get_upload_url.json'),
			MockResponse::fromFile('upload.json'),
		);

		$content = 'The quick brown box jumps over the lazy dog';

		$file = $this->client->uploadFile(
			'test.txt',
			'bucketId',
			$content
		);

		static::assertInstanceOf(File::class, $file);

		$this->guzzler->expects($this->once())
			->post('https://pod-000-1005-03.backblaze.com/b2api/v2/b2_upload_file?cvt=c001_v0001005_t0027&bucket=4a48fe8875c6214145260818')
			->withHeader('Authorization', 'authToken')
			->withHeader('Content-Length', strlen($content))
			->withHeader(File::HEADER_X_BZ_FILE_NAME, 'test.txt')
			->withHeader(File::HEADER_X_BZ_CONTENT_SHA1, sha1($content))
			->withBody($content);
	}

	public function testUploadingWithCustomContentTypeAndLastModified()
	{
		$this->guzzler->queueResponse(
			MockResponse::fromFile('get_upload_url.json'),
			MockResponse::fromFile('upload.json'),
		);

		$content = 'Test file content';
		$lastModified =  701568000000;
		$contentType = 'text/plain';

		$file = $this->client->uploadFile(
			'test.txt',
			'bucketId',
			$content,
			$contentType,
			[
				'src_last_modified_millis' => $lastModified
			]
		);

		static::assertInstanceOf(File::class, $file);

		// We'll also check the Guzzle history to make sure the upload request got created correctly.
		$this->guzzler->expects($this->once())
			->post('https://pod-000-1005-03.backblaze.com/b2api/v2/b2_upload_file?cvt=c001_v0001005_t0027&bucket=4a48fe8875c6214145260818')
			->withHeader('Authorization', 'authToken')
			->withHeader('Content-Length', strlen($content))
			->withHeader(File::HEADER_X_BZ_FILE_NAME, 'test.txt')
			->withHeader(File::HEADER_X_BZ_CONTENT_SHA1, sha1($content))
			->withHeader(FileInfo::HEADER_PREFIX.FileInfo::B2_FILE_INFO_MTIME, (string) $lastModified)
			->withBody($content);
	}
	
}
