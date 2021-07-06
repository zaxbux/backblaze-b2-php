<?php

namespace Zaxbux\BackblazeB2\Http\Middleware;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\RequestInterface;
use Zaxbux\BackblazeB2\Config;

class ApplyAuthorizationMiddleware
{
	private $config;

	public function __construct(Config $config)
	{
		$this->config = $config;
	}

	public function __invoke(callable $next): callable
	{
		return function (RequestInterface $request, array $options = []) use ($next) {
			// Don't apply token to account authorization requests
			if (!preg_match('/\/b2_authorize_account$/', rtrim($request->getUri()->getPath(), '/'))) {
				$request = $this->applyToken($request);
			}
			
			
			return $next($request, $options);
		};
	}

	/**
	 * @param RequestInterface $request 
	 */
	protected function applyToken(RequestInterface $request): RequestInterface
	{
		$request = Utils::modifyRequest($request, [
			'uri' => new Uri($this->config->accountAuthorization()->getApiUrl() . $request->getUri()->getPath()),
			'set_headers' => [
				'Authorization' => $this->config->accountAuthorization()->getAuthorizationToken(),
			],
		]);

		return $request;
	}
}
