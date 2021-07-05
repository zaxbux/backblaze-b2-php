<?php

namespace tests;

class Utils {
	public static function nowInMilliseconds(): int
	{
		return round(microtime(true) * 1000);
	}
}