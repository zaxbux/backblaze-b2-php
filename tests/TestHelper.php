<?php

namespace Zaxbux\BackblazeB2\Tests;

use Zaxbux\BackblazeB2\Http\ClientFactory;
use Zaxbux\BackblazeB2\Http\Config;

trait TestHelper {
	protected function buildGuzzleFromResponses(array $responses, $history = null) {
		$mock = new \GuzzleHttp\Handler\MockHandler($responses);
		$handler = new \GuzzleHttp\HandlerStack($mock);

		if ($history) {
			$handler->push($history);
		}

		return ClientFactory::create(null, $handler);
	}

	protected function buildResponseFromStub($statusCode, array $headers = [], $responseFile) {
		$response = file_get_contents(dirname(__FILE__).'/responses/'.$responseFile);

		return new \GuzzleHttp\Psr7\Response($statusCode, $headers, $response);
	}
}
