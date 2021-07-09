<?php

namespace Zaxbux\BackblazeB2\Http\Middleware;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Utils as Psr7Utils;
use Psr\Http\Message\RequestInterface;
use Zaxbux\BackblazeB2\Client;
use Zaxbux\BackblazeB2\Utils;

/** @package BackblazeB2\Http\Middleware */
class ApplyAuthorizationMiddleware
{
	private $client;

	public function __construct(Client $client)
	{
		$this->client = $client;
	}

	public function __invoke(callable $next): callable
	{
		return function (RequestInterface $request, array $options = []) use ($next) {
			//fwrite(STDERR, print_r($request, true));

			// Don't apply token to account authorization requests
			//if (!preg_match('/\/b2_authorize_account$/', rtrim($request->getUri()->getPath(), '/'))) {

			// Add token to requests without any authorization
			if (empty($request->getHeader('Authorization'))) {
				if (!$this->client->accountAuthorization() || $this->client->accountAuthorization()->expired()) {
					$this->client->refreshAccountAuthorization();
				}

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
		$request = Psr7Utils::modifyRequest($request, [
			'uri' => new Uri(Utils::joinPaths($request->getUri()->getHost() ? '' : $this->client->accountAuthorization()->apiUrl(), (string)$request->getUri())),
			'set_headers' => [
				'Authorization' => $this->client->accountAuthorization()->authorizationToken(),
			],
		]);

		return $request;
	}
}
