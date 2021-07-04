<?php

namespace Zaxbux\BackblazeB2\Http;

//use GuzzleHttp\Client;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Zaxbux\BackblazeB2\Client as B2Client;
use Zaxbux\BackblazeB2\B2\Object\AccountAuthorization;
use Zaxbux\BackblazeB2\Http\Middleware\ExceptionMiddleware;
use Zaxbux\BackblazeB2\Http\Middleware\RefreshAuthorizationMiddleware;

/** @package Zaxbux\BackblazeB2\Http */
class ClientFactory {

	/**
	 * 
	 * @param AccountAuthorization $accountAuthorization 
	 * @param Config               $config 
	 * @param mixed                $handler 
	 * @return ClientInterface 
	 */
	public static function create(
		Config $config,
		$handler = null
	): ClientInterface {
		$stack = $handler ?? HandlerStack::create(); //HandlerStack::create($handler);

		foreach ($config->middleware ?? [] as $name => $middleware) {
			$stack->push($middleware, $name ?? '');
		}

		$client = new Client([
			'http_errors' => $config->useHttpErrors ?? false,
			'handler' => $stack,
			'headers' => [
				'Accept' => 'application/json, */*;q=0.8',
				'Content-Type' => 'application/json; charset=utf-8',
				'User-Agent' => sprintf(
					'backblaze-b2-php/%s+php/%s github.com/zaxbux/backblaze-b2-php',
					B2Client::B2_API_CLIENT_VERSION,
					PHP_VERSION
				),
			],
		]);

		$stack->push(new ExceptionMiddleware());
		$stack->push(new RefreshAuthorizationMiddleware($config, $client));

		/*$stack->push(Middleware::retry(function ($retries, $request, $response = null) use ($config) {
			return $retries <= $config->maxRetries; //&& $this->isRetryable($request);
		}, function ($retries, $response) {
			return $retries * 1000;
		}));*/

		return $client;
	}

	public function isRetryable(RequestInterface $request) {
		return true;
	}
}