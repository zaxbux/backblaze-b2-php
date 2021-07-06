<?php

namespace Zaxbux\BackblazeB2\Http;

//use GuzzleHttp\Client;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Zaxbux\BackblazeB2\Client as B2Client;
use Zaxbux\BackblazeB2\Config;
use Zaxbux\BackblazeB2\Object\AccountAuthorization;
use Zaxbux\BackblazeB2\Http\Middleware\ExceptionMiddleware;
use Zaxbux\BackblazeB2\Http\Middleware\ApplyAuthorizationMiddleware;

/** @package Zaxbux\BackblazeB2\Http */
class ClientFactory {

	public const RETRY_POWER = 3;

	/**
	 * Creates a new instance of a GuzzleHttp client.
	 * 
	 * @param Config $config 
	 * @return ClientInterface 
	 */
	public static function create(Config $config): ClientInterface {
		$stack = $config->handler();
		//$stack = $handler ?? HandlerStack::create(); //HandlerStack::create($handler);

		foreach ($config->middleware ?? [] as $name => $middleware) {
			$stack->push($middleware, $name ?? '');
		}

		$client = new Client([
			'http_errors' => $config->useHttpErrors ?? false,
			'handler' => $stack,
			'headers' => [
				'Accept'       => 'application/json, */*;q=0.8',
				'Content-Type' => 'application/json; charset=utf-8',
				'User-Agent'   => static::getUserAgent(),
			],
		]);

		$stack->push(new ExceptionMiddleware());
		$stack->push(new ApplyAuthorizationMiddleware($config, $client));
		$stack->push(Middleware::retry(static::retryDecider($config), static::retryDelay($config)));

		return $client;
	}

	/**
	 * Creates an anonymous function that decides if a request should be retried.
	 * 
	 * @param Config $config 
	 * @return callable 
	 */
	public static function retryDecider(Config $config): callable {
		return static function(
			int $retries,
			RequestInterface $request,
			ResponseInterface $response = null,
			$exception = null
		) use ($config): bool {
			// Do not retry `b2_authorize_account` requests.
			/*if (preg_match('/b2_authorize_account$/', rtrim($request->getUri()->getPath(), '/'))) {
				return false;
			}*/

			$overRetryLimit = ($retries > $config->maxRetries());
			$statusCode = $response->getStatusCode() ?? null;

			return !$overRetryLimit && (
				$statusCode === Response::HTTP_TOO_MANY_REQUESTS ||
				$statusCode === Response::HTTP_SERVICE_UNAVAILABLE
			);
		};
	}

	/**
	 * Creates an anonymous function that returns a time,
	 * in milliseconds, to wait before making another attempt.
	 * 
	 * @param Config $config 
	 * @return callable 
	 */
	public static function retryDelay(Config $config): callable {
		return static function(int $retries, ResponseInterface $response) use ($config): int {
			// Set a default delay
			$delay = $config->maxRetryDelay(); //$config->maxRetries() ** static::RETRY_POWER;

			// Use the value of the `Retry-After` header
			if ($response->getStatusCode() === Response::HTTP_TOO_MANY_REQUESTS) {
				$delay = (int) $response->getHeader('Retry-After')[0] ?? $delay;
			}

			// Exponential back-off
			if ($response->getStatusCode() === Response::HTTP_SERVICE_UNAVAILABLE) {
				$delay = $retries ** static::RETRY_POWER;
			}

			// Convert to milliseconds
			return $delay * 1000;
		};
	}

	public static function getUserAgent(): string
	{
		return sprintf(
			'backblaze-b2-php/%s+php/%s github.com/zaxbux/backblaze-b2-php',
			B2Client::B2_API_CLIENT_VERSION,
			PHP_VERSION
		);
	}
}