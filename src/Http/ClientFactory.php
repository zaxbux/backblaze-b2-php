<?php

namespace Zaxbux\BackblazeB2\Http;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Zaxbux\BackblazeB2\Client as B2Client;
use Zaxbux\BackblazeB2\Client\RefreshAuthorizationMiddleware;

/** @package Zaxbux\BackblazeB2\Http */
class ClientFactory {
	/**
	 * @param Config|null $config 
	 * @return static 
	 */
	public static function create(Config $config = null) {
		$stack = HandlerStack::create();

		foreach ($config->middleware as $name => $middleware) {
			$stack->push($middleware, $name ?? '');
		}

		$client = new Client([
			'base_uri' => $config->baseUri,
			'http_errors' => $config->useHttpErrors,
			//'exceptions' => false,
			'handler' => $stack,
			'headers' => [
				'Accept' => 'application/json, */*;q=0.8',
				'Content-Type' => 'application/json; charset=utf-8',
				'User-Agent' => sprintf(
					'backblaze-b2-php/%s+php/%s github.com/zaxbux/backblaze-b2-php',
					B2Client::CLIENT_VERSION,
					PHP_VERSION
				),
			],
		]);

		$stack->push(new ExceptionMiddleware());
		$stack->push(new RefreshAuthorizationMiddleware($client, $config));

		$stack->push(Middleware::retry(function ($retries, $request, $response = null) use ($config) {
			return $retries <= $config->maxRetries && $this->isRetryable($request);
		}, function ($retries, $response) {
			return $retries * 1000;
		}));

		return new static($client, $config);
	}

	public function isRetryable(RequestInterface $request) {
		return true;
	}
}