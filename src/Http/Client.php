<?php

namespace Zaxbux\BackblazeB2\Http;

use Zaxbux\BackblazeB2\ErrorHandler;
use GuzzleHttp\Client as GuzzleClient;

/**
 * Client wrapper around Guzzle.
 *
 * @package Zaxbux\BackblazeB2\Http
 */
class Client extends GuzzleClient {
	
	/**
	 * Sends a response to the B2 API, automatically handling decoding JSON and errors.
	 *
	 * @param string $method  The HTTP requeest method.
	 * @param string $uri     The request URI.
	 * @param array  $options Guzzle options.
	 * @param bool   $asJson  Return JSON, default is true.
	 * 
	 * @return mixed
	 */
	public function request($method, $uri = null, array $options = [], bool $asJson = true) {
		$response = parent::request($method, $uri, $options);

		if ($response->getStatusCode() !== 200) {
			ErrorHandler::handleErrorResponse($response);
		}

		return $asJson ? json_decode($response->getBody(), true) : $response;
	}
}
