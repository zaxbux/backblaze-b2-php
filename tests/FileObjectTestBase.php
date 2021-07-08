<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use Zaxbux\BackblazeB2\Object\File;
use Zaxbux\BackblazeB2\Object\File\FileInfo;
use Zaxbux\BackblazeB2\Object\File\FileLock;
use Zaxbux\BackblazeB2\Object\File\ServerSideEncryption;
use Zaxbux\BackblazeB2\Object\File\FileActionType;
use Zaxbux\BackblazeB2\Classes\FilePathInfo;

abstract class FileObjectTestBase extends TestCase
{
	protected static function getFileInit(): array
	{
		return [
			File::ATTRIBUTE_FILE_ID => 'fileId',
			File::ATTRIBUTE_FILE_NAME => 'directory/file.extension',
			File::ATTRIBUTE_BUCKET_ID => 'bucketId',
			File::ATTRIBUTE_ACTION => FileActionType::UPLOAD,
			File::ATTRIBUTE_FILE_INFO => [
				FileInfo::B2_FILE_INFO_MTIME => Utils::nowInMilliseconds(),
			],
			File::ATTRIBUTE_CONTENT_LENGTH => 1024,
			File::ATTRIBUTE_CONTENT_TYPE => File::CONTENT_TYPE_AUTO,
			File::ATTRIBUTE_CONTENT_SHA1 => md5('backblaze'),
			File::ATTRIBUTE_CONTENT_MD5 => sha1('backblaze'),
			File::ATTRIBUTE_UPLOAD_TIMESTAMP => Utils::nowInMilliseconds(),
			File::ATTRIBUTE_ACCOUNT_ID => 'accountId',
			File::ATTRIBUTE_FILE_RETENTION => [],
			File::ATTRIBUTE_LEGAL_HOLD => [],
			File::ATTRIBUTE_SSE => [],
			File::ATTRIBUTE_PART_NUMBER => null
		];
	}

	protected static function isFileObject($file): void
	{
		static::assertInstanceOf(File::class, $file);
		static::assertIsString($file->id());
		static::assertIsString($file->name());
		static::assertIsString($file->bucketId());
		static::assertInstanceOf(FileActionType::class, $file->action());
		static::assertEquals(FileActionType::UPLOAD, $file->action());
		static::assertInstanceOf(FileInfo::class, $file->info());
		static::assertIsInt($file->contentLength());
		static::assertIsString($file->contentType());
		static::assertEquals($file->contentMd5(), md5('backblaze'));
		static::assertEquals($file->contentSha1(), sha1('backblaze'));
		static::assertIsString($file->accountId());
		//static::assertInstanceOf(FileLock::class, $file->fileLock());
		static::assertInstanceOf(ServerSideEncryption::class, $file->serverSideEncryption());
		static::assertEquals($file->partNumber(), null);
		static::assertInstanceOf(FilePathInfo::class, $file->pathInfo());
		static::assertEqualsWithDelta(Utils::nowInMilliseconds(), $file->uploadTimestamp(), 100);
		static::assertEqualsWithDelta(Utils::nowInMilliseconds(), $file->lastModifiedTimestamp(), 100);
		static::assertEquals('extension', $file->pathInfo()->extension);
		static::assertJson(json_encode($file));
	}
}
