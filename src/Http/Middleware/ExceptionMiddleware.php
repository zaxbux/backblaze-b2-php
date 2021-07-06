<?php

namespace Zaxbux\BackblazeB2\Http\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zaxbux\BackblazeB2\Exceptions\B2APIException;
use Zaxbux\BackblazeB2\Exceptions\NotFoundException;
use Zaxbux\BackblazeB2\Exceptions\UnauthorizedException;
use Zaxbux\BackblazeB2\Http\ErrorHandler;
use Zaxbux\BackblazeB2\Http\Response;

class ExceptionMiddleware
{
	public function __invoke(callable $handler)
	{
		return function (RequestInterface $request, array $options = []) use ($handler) {
			$promise = $handler($request, $options);
			
			return $promise->then(function (ResponseInterface $response) {
				if ($this->isSuccessful($response)) {
					return $response;
				}
				$this->handleErrorResponse($response);
			});
		};
	}

	public function isSuccessful(ResponseInterface $response)
	{
		return $response->getStatusCode() <= 299;
		//return $response->getStatusCode() == Response::HTTP_OK || Response::HTTP_NO_CONTENT || Response::HTTP_PARTIAL_CONTENT;
		//return $response->getStatusCode() < Response::HTTP_BAD_REQUEST;
	}

	public function handleErrorResponse(ResponseInterface $response)
	{
		throw ErrorHandler::getException($response);
		/*
		switch ($response->getStatusCode()) {
			//case Response::HTTP_UNPROCESSABLE_ENTITY:
			//	throw new ValidationException(Utils::jsonDecode($response->getBody(), true));
			case Response::HTTP_NOT_FOUND:
				throw new NotFoundException;
			case Response::HTTP_UNAUTHORIZED:
				throw new UnauthorizedException;
			default:
				throw ErrorHandler::getException($response);
				//throw new B2APIException((string) $response->getBody());
		}
		*/
	}
}
