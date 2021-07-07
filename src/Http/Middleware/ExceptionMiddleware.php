<?php

namespace Zaxbux\BackblazeB2\Http\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zaxbux\BackblazeB2\Http\ErrorHandler;
use Zaxbux\BackblazeB2\Http\Exceptions\TooManyRequestsException;
use Zaxbux\BackblazeB2\Http\StatusCode;

/** @package Zaxbux\BackblazeB2\Http\Middleware */
class ExceptionMiddleware
{
	public function __invoke(callable $handler)
	{
		return static function (RequestInterface $request, array $options = []) use ($handler) {
			$promise = $handler($request, $options);
			
			return $promise->then(function (ResponseInterface $response) use($request) {
				if (static::isSuccessful($response)) {
					return $response;
				}

				if ($response->getStatusCode() === StatusCode::HTTP_TOO_MANY_REQUESTS) {
					throw new TooManyRequestsException('', $request, $response);
				}

				throw ErrorHandler::getException($request, $response);
			});
		};
	}

	public static function isSuccessful(ResponseInterface $response)
	{
		return $response->getStatusCode() >= StatusCode::HTTP_OK && $response->getStatusCode() <= StatusCode::HTTP_PARTIAL_CONTENT;
	}
}
