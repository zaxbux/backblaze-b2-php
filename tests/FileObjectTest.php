<?php

declare(strict_types=1);

namespace tests;

use Zaxbux\BackblazeB2\B2\Object\File;

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
}
