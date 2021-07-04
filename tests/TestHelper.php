<?php

namespace Zaxbux\BackblazeB2\Tests;

trait TestHelper
{
	protected function buildGuzzleHandlerFromResponses(array $responses, $history = null)
	{
		$mock = new \GuzzleHttp\Handler\MockHandler($responses);

		return $mock;
	}

	protected function buildResponseFromStub($statusCode, array $headers = [], $responseFile)
	{
		$response = file_get_contents(dirname(__FILE__) . '/responses/' . $responseFile);

		return new \GuzzleHttp\Psr7\Response($statusCode, $headers, $response);
	}
}
