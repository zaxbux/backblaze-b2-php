<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Http\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zaxbux\BackblazeB2\Config;
use Zaxbux\BackblazeB2\Http\Response;

class RetryMiddleware
{
	public const RETRY_POWER = 2;

	/** @var \Zaxbux\BackblazeB2\Config */
	protected $config;

	public function __construct(Config $config)
	{
		$this->config = $config;
	}

	public function __invoke($handler)
	{
		return new \GuzzleHttp\RetryMiddleware(static::retryDecider($this->config), $handler, static::retryDelay($this->config));
	}

	/**
	 * Creates an anonymous function that decides if a request should be retried.
	 * 
	 * @param Config $config 
	 * @return callable 
	 */
	private static function retryDecider(Config $config): callable {
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
	private static function retryDelay(Config $config): callable {
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
}
