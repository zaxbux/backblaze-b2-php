<?php

namespace tests;

use GuzzleHttp\Psr7\Response;

final class MockResponse {
	private const RESPONSE_FILE_DIRECTORY = 'responses';

	private $statusCode;
	private $headers;
	private $body;

	public function __construct($statusCode, $headers, $body)
	{
		$this->statusCode = $statusCode;
		$this->headers = $headers;
		$this->body = $body;
	}

	public function __invoke()
	{
		return new Response($this->statusCode, $this->headers, $this->body);
	}

	public static function fromFile(
		string $filePath,
		?int $statusCode = 200,
		?array $headers = []
	): MockResponse {
		$body = file_get_contents(static::joinPaths(dirname(__FILE__), static::RESPONSE_FILE_DIRECTORY,  $filePath));

		return new static($statusCode, $headers, $body);
	}

	/**
	 * @param string[] $paths 
	 * @return string 
	 */
	private static function joinPaths(...$paths)
	{
		$paths = array_filter(array_map(function ($path) {
			return rtrim($path, DIRECTORY_SEPARATOR);
		}, $paths));

		return join(DIRECTORY_SEPARATOR, $paths);
	}
}