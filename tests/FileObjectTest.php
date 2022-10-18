<?php

declare(strict_types=1);

namespace tests;

use Zaxbux\BackblazeB2\Object\File;
use Zaxbux\BackblazeB2\Object\File\FileInfo;

class FileObjectTest extends FileObjectTestBase
{
	public function testNewFileObject()
	{
		static::isFileObject(new File(...array_values(static::getFileInit())));
	}

	public function testCreateFileObjectFromArray()
	{
		static::isFileObject(File::fromArray(static::getFileInit()));
	}

	public function testLastModifiedTimestampString()
	{
		$init = static::getFileInit();
		// If the last modified time was set as a string, it should be converted to an int
		$init[File::ATTRIBUTE_FILE_INFO][FileInfo::B2_FILE_INFO_MTIME] = '1234';
		$file = File::fromArray($init);
		static::assertEquals(1234, $file->lastModifiedTimestamp());
	}

	public function testLastModifiedTimestampNull()
	{
		$init = static::getFileInit();
		// If the last modified time does not exist, it should be returned as null
		unset($init[File::ATTRIBUTE_FILE_INFO][FileInfo::B2_FILE_INFO_MTIME]);
		$file = File::fromArray($init);
		static::assertEquals(null, $file->lastModifiedTimestamp());
	}
}
