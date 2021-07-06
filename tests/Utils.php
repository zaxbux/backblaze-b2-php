<?php

namespace tests;

use Zaxbux\BackblazeB2\Client;
use Zaxbux\BackblazeB2\Utils as ClientUtils;

class Utils {
	public static function nowInMilliseconds(): int
	{
		return round(microtime(true) * 1000);
	}

	public static function dumpHistory($history)
	{
		foreach ($history as $item) {
			print_r($item['request']);
			print_r($item['response']);
			print_r((string)$item['response']->getBody());
			print("\n--------------------\n");
		}
	}
}