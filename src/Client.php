<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use Zaxbux\BackblazeB2\Config;
use Zaxbux\BackblazeB2\Http\Middleware\ApplyAuthorizationMiddleware;
use Zaxbux\BackblazeB2\Http\Middleware\ExceptionMiddleware;
use Zaxbux\BackblazeB2\Http\Middleware\RetryMiddleware;
use Zaxbux\BackblazeB2\Interfaces\AuthorizationCacheInterface;
use Zaxbux\BackblazeB2\Object\AccountAuthorization;
use Zaxbux\BackblazeB2\Operations\ApplicationKeyOperationsTrait;
use Zaxbux\BackblazeB2\Operations\BucketOperationsTrait;
use Zaxbux\BackblazeB2\Operations\FileOperationsTrait;

/**
 * API Client for Backblaze B2.
 * 
 * @package Zaxbux\BackblazeB2
 */
class Client
{
	public const VERSION  = '2.0.0';
	public const USER_AGENT_PREFIX = 'backblaze-b2-php/';
	public const BASE_URI = 'https://api.backblazeb2.com';
	public const B2_API_VERSION = '/b2api/v2';

	use FileOperationsTrait;
	use BucketOperationsTrait;
	use ApplicationKeyOperationsTrait;

	/** @var \Zaxbux\BackblazeB2\Config */
	protected $config;

	/** @var \GuzzleHttp\ClientInterface */
	protected $http;

	/** @var \Zaxbux\BackblazeB2\Object\AccountAuthorization */
	private $accountAuthorization;

	/**
	 * Get the configuration object for this instance.
	 */
	public function getConfig(): Config
	{
		return $this->config;
	}

	/**
	 * Get the HTTP client for this instance.
	 */
	public function getHttpClient(): ClientInterface
	{
		if (!$this->http) {
			$this->http = $this->createDefaultHttpClient();
		}

		return $this->http;
	}

	public function accountAuthorization(): ?AccountAuthorization
	{
		return $this->accountAuthorization;
	}

	/**
	 * Create a new instance of the B2 API client for PHP.
	 * 
	 * @param array|Config $config One of three possible values:
	 *                               1. An array with application keys: `["application_key_id", "application_key"]`
	 *                               2. An associative array of config options
	 *                               3. An instance of a configuration object (@see \Zaxbux\BackblazeB2\Config)
	 */
	public function __construct($config)
	{
		$this->config = Config::fromArray($config);

		$this->getHttpClient();
	}

	/**
	 * Authorize the B2 account in order to get an authorization token and API URLs.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_authorize_account.html
	 * 
	 * @param string $applicationKeyId 
	 * @param string $applicationKey 
	 * @param ClientInterface $client 
	 */
	public function authorizeAccount(): AccountAuthorization {
		$response = $this->http->request('GET', Client::BASE_URI . Client::B2_API_VERSION . '/b2_authorize_account', [
			'headers' => [
				'Authorization' => Utils::basicAuthorization($this->config->applicationKeyId(), $this->config->applicationKey()),
			],
		]);

		return AccountAuthorization::fromResponse($response);
	}

	public function refreshAccountAuthorization() {
		// Check cache for account authorization if account is not already authorized.
		if (!$this->accountAuthorization && $this->config->authorizationCache() instanceof AuthorizationCacheInterface) {
			$this->accountAuthorization = $this->config->authorizationCache()->get($this->config->applicationKeyId());
		}

		// Refresh the token if it wasn't cached, or if it has expired.
		if (!$this->accountAuthorization || $this->accountAuthorization->expired()) {
			$this->accountAuthorization = $this->authorizeAccount();

			// Cache the new key
			if ($this->config->authorizationCache() instanceof AuthorizationCacheInterface) {
				$this->config->authorizationCache()->put($this->config->applicationKeyId(), $this->accountAuthorization);
			}
		}

		// If account authorization still doesn't exist, there is an issue.
		if (!$this->accountAuthorization) {
			throw new Exception('Failed to refresh account authorization.');
		}
	}

	/**
	 * Creates a new instance of a GuzzleHttp client.
	 */
	protected function createDefaultHttpClient(): ClientInterface {
		$stack = $this->config->handler();

		/*foreach ($this->config->middleware() as $name => $middleware) {
			$stack->push($middleware, $name ?? '');
		}*/

		$stack->push(new ExceptionMiddleware(), 'exception_handler');
		$stack->push(new ApplyAuthorizationMiddleware($this), 'b2_auth');
		$stack->push(new RetryMiddleware($this->config), 'retry');

		$client = new GuzzleClient([
			'base_uri' => Client::B2_API_VERSION,
			'http_errors' => $this->config->useHttpErrors ?? false,
			'allow_redirects' => false,
			'handler' => $stack,
			'headers' => [
				//'Accept'       => 'application/json, */*;q=0.8',
				'User-Agent'   => Utils::getUserAgent($this->config->applicationName()),
			],
		]);

		return $client;
	}

	/**
	 * @see __construct()
	 */
	public static function instance($config): Client
	{
		return new static($config);
	}
}
