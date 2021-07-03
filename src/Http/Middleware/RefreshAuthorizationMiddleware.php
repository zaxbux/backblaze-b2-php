<?php

namespace Zaxbux\BackblazeB2\Http\Middleware;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\RequestInterface;
use Zaxbux\BackblazeB2\Client\AccountAuthorization;
use Zaxbux\BackblazeB2\Http\Config;

//use function \GuzzleHttp\json_decode;

class RefreshAuthorizationMiddleware
{
	private $client;
	private $config;

	public function __construct(Client $client, Config $config = null)
	{
		$this->client = $client;
		$this->config = $config;
	}

	public function __invoke(callable $next): callable
	{
		return function (RequestInterface $request, array $options = []) use ($next) {
			$request = $this->applyToken($request);
			
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
			'uri' => $this->config->client->getAccountAuthorization()->getApiUrl(),
			'set_headers' => [
				'Authorization' => $this->getToken(),
			],
		]);
	}

	private function getToken(): string
	{
		return $this->config->client->getAccountAuthorization()->getAuthorizationToken();
	}

	private function hasValidToken(): bool
	{
		return false;
		return $this->config->client->getAccountAuthorization() instanceof AccountAuthorization && !$this->config->client->getAccountAuthorization()->isStale();
	}

	private function acquireAccessToken(): void
	{
		/*$parameters = $this->getTokenRequestParameters();
		$response = $this->client->request('POST', $this->config->getTokenRoute(), [
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
		$accountAuthorization = AccountAuthorization::refresh(
			$this->config->client->getApplicationKeyId(),
			$this->config->client->getApplicationKey(),
			$this->client
		);

		$this->config->client->setAccountAuthorization($accountAuthorization);
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
