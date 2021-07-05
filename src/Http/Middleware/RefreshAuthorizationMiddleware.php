<?php

namespace Zaxbux\BackblazeB2\Http\Middleware;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\RequestInterface;
use Zaxbux\BackblazeB2\Object\AccountAuthorization;
use Zaxbux\BackblazeB2\Config;

class RefreshAuthorizationMiddleware
{
	private $client;
	private $config;

	public function __construct(Config $config, ClientInterface $client)
	{
		$this->client = $client;
		$this->config = $config;
	}

	public function __invoke(callable $next): callable
	{
		return function (RequestInterface $request, array $options = []) use ($next) {
			if (strpos($request->getUri()->getPath(), '/b2_authorize_account') < 0) {
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
		if (!$this->hasValidToken()) {
			$this->acquireAccessToken();
		}

		return Utils::modifyRequest($request, [
			'uri' => $this->config->auth->getApiUrl(),
			'set_headers' => [
				'Authorization' => $this->getToken(),
			],
		]);
	}

	private function getToken(): string
	{
		return $this->config->auth->getAuthorizationToken();
	}

	private function hasValidToken(): bool
	{
		
		return $this->config->auth instanceof AccountAuthorization && !$this->config->auth->isStale();
	}

	private function acquireAccessToken(): void
	{
		$this->config->auth->refresh($this->client);

		/*$parameters = $this->getTokenRequestParameters();
		$response = $this->guzzle->request('POST', $this->config->getTokenRoute(), [
			'form_params' => $parameters,
			// We'll use the default handler so we don't rerun our middleware
			'handler' => HandlerStack::create(),
		]);
		$response = json_decode((string) $response->getBody(), true);
		$this->token = new BearerToken(
			$response['access_token'],
			(int) $response['expires_in'],
			$response['refresh_token']
		);*/
		//$accountAuthorization = AccountAuthorization::refresh();

		//$this->config->client->setAccountAuthorization($accountAuthorization);
	}
	
	/*private function getTokenRequestParameters(): array
	{
		if ($this->getToken() and $this->getToken()->isRefreshable()) {
			return [
				'grant_type' => 'refresh_token',
				'refresh_token' => $this->getToken()->refreshToken()
			];
		}
		return [
			'grant_type' => 'password',
			'username' => $this->config->username(),
			'password' => $this->config->password()
		];
	}*/
}
