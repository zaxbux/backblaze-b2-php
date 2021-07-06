<?php

namespace Zaxbux\BackblazeB2\Exceptions\Request;

use GuzzleHttp\Exception\RequestException;
use JsonException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class B2APIException extends RequestException {

	private $statusCode;

	public function __construct(
		RequestInterface $request,
		ResponseInterface $response,
		?Throwable $previous = null
	) {
		$message = $response->getReasonPhrase();
		$code = $response->getStatusCode();

		try {
			$responseJson = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

			$this->statusCode = $responseJson['status'] ?? null;
			$message = $responseJson['message'] ?? null;
			$code = $responseJson['code'] ?? null;
		} catch (JsonException $e) {
			// Ignore JSON exceptions, response object is available instead.
		}

		parent::__construct($message, $request, $response, $previous);
		$this->code = $code;
	}

	/**
	 * Get the status code as returned by the B2 API.
	 */ 
	public function getStatus(): ?string
	{
		return $this->statusCode;
	}
}
