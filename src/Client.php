<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use Zaxbux\BackblazeB2\Config;
use Zaxbux\BackblazeB2\Helpers\ApplicationKeyOperationsHelper;
use Zaxbux\BackblazeB2\Helpers\BucketOperationsHelper;
use Zaxbux\BackblazeB2\Helpers\FileOperationsHelper;
use Zaxbux\BackblazeB2\Http\Endpoint;
use Zaxbux\BackblazeB2\Http\Middleware\{
	ApplyAuthorizationMiddleware,
	ExceptionMiddleware,
	RetryMiddleware,
};
use Zaxbux\BackblazeB2\Operations\{
	ApplicationKeyOperationsTrait,
	BucketOperationsTrait,
	DownloadOperationsTrait,
	FileOperationsTrait,
	LargeFileOperationsTrait,
	UploadOperationsTrait,
};
use Zaxbux\BackblazeB2\Interfaces\AuthorizationCacheInterface;
use Zaxbux\BackblazeB2\Object\AccountAuthorization;
use Zaxbux\BackblazeB2\Object\Bucket;
use Zaxbux\BackblazeB2\Object\File;
use Zaxbux\BackblazeB2\Object\Key;
use Zaxbux\BackblazeB2\Traits\ApplyToAllFileVersionsTrait;

/**
 * API Client for Backblaze B2.
 * 
 * @package BackblazeB2
 */
class Client
{
	public const VERSION  = '2.0.0';
	public const USER_AGENT_PREFIX = 'backblaze-b2-php/';
	public const BASE_URI = 'https://api.backblazeb2.com/';
	public const B2_API_VERSION = 'b2api/v2/';

	use FileOperationsTrait;
	use LargeFileOperationsTrait;
	use BucketOperationsTrait;
	use ApplicationKeyOperationsTrait;
	use UploadOperationsTrait;
	use DownloadOperationsTrait;
	use ApplyToAllFileVersionsTrait;

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

	public function accountAuthorization(): AccountAuthorization
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
	public function authorizeAccount(): AccountAuthorization
	{
		$response = $this->http->request('GET', Client::BASE_URI . Client::B2_API_VERSION . Endpoint::AUTHORIZE_ACCOUNT, [
			'headers' => [
				'Authorization' => Utils::basicAuthorization($this->config->applicationKeyId(), $this->config->applicationKey()),
			],
		]);

		return AccountAuthorization::fromResponse($response);
	}

	public function refreshAccountAuthorization()
	{
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
	protected function createDefaultHttpClient(): ClientInterface
	{
		$stack = $this->config->handler();

		/*foreach ($this->config->middleware() as $name => $middleware) {
			$stack->push($middleware, $name ?? '');
		}*/

		$stack->push(new ExceptionMiddleware(), 'exception_handler');
		$stack->push(new ApplyAuthorizationMiddleware($this), 'b2_authorization');
		$stack->push(new RetryMiddleware($this->config), 'retry');

		$client = new GuzzleClient([
			'base_uri' => Client::B2_API_VERSION,
			'http_errors' => $this->config->useHttpErrors ?? false,
			'allow_redirects' => false,
			'handler' => $stack,
			'headers' => [
				'User-Agent'   => Utils::getUserAgent($this->config->applicationName()),
			],
		]);

		return $client;
	}

	public function allowedBucketId(): ?string
	{
		return $this->accountAuthorization->allowed('bucketId') ?? null;
	}

	public function allowedBucketName(): ?string
	{
		return $this->accountAuthorization->allowed('bucketName') ?? null;
	}

	/**
	 * Helper method for bucket operations.
	 * 
	 * @param null|Bucket $bucket The bucket to perform operations on.
	 */
	public function bucket(?Bucket $bucket = null): BucketOperationsHelper
	{
		return BucketOperationsHelper::instance($this)->withBucket($bucket);
	}

	/**
	 * Helper method for file operations.
	 * 
	 * @param null|File $file The file to perform operations on.
	 */
	public function file(?File $file = null): FileOperationsHelper
	{
		return FileOperationsHelper::instance($this)->withFile($file);
	}

	/**
	 * Helper method for application key operations.
	 * 
	 * @param null|Key $key The key to perform operations on.
	 */
	public function applicationKey(?Key $key = null): ApplicationKeyOperationsHelper
	{
		return ApplicationKeyOperationsHelper::instance($this)->withApplicationKey($key);
	}

	/**
	 * @see __construct()
	 */
	public static function instance($config): Client
	{
		return new static($config);
	}
}
