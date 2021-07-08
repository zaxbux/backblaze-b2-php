<?php

declare(strict_types=1);

namespace tests;

use GuzzleHttp\Psr7\Response;
use Zaxbux\BackblazeB2\Object\Key;
use Zaxbux\BackblazeB2\Response\KeyList;

class KeyObjectTest extends KeyObjectTestBase
{
	public function testNewKeyObject()
	{
		static::isKeyObject(new Key(...array_values(static::getKeyInit())));
	}

	public function testCreateKeyObjectFromArray()
	{
		static::isKeyObject(Key::fromArray(static::getKeyInit()));
	}

	public function testKeyList()
	{
		$keys = static::createKeys(10);

		$keyList = new KeyList($keys);

		static::isKeyList($keyList);
	}

	public function testKeyListFromResponse()
	{
		$keyList = KeyList::fromResponse(new Response(200, [], json_encode([
			KeyList::ATTRIBUTE_KEYS => static::createKeys(10),
			KeyList::ATTRIBUTE_NEXT_APPLICATION_KEY_ID => null,
		])));

		static::isKeyList($keyList);
	}
}
