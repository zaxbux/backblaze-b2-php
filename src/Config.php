<?php

namespace Zaxbux\BackblazeB2;

use GuzzleHttp\HandlerStack;
use Zaxbux\BackblazeB2\Classes\BuiltinAuthorizationCache;
use Zaxbux\BackblazeB2\Interfaces\AuthorizationCacheInterface;

/**
 * The main configuration object for the client.
 * 
 * @package BackblazeB2
 */
class Config
{
	public const DEFAULTS = [
		'applicationName' => '',
		//'handler' => null,
		'middleware' => [],
		'useHttpErrors' => null,
		'maxRetries' => 4,
		'maxRetryDelay' => 64,
		'maxFileCount' => 1000,
		'maxKeyCount' => 1000,
		'useSSEHeaders' => false,
	];

	/**
	 * The identifier for the key. The account ID can also be used.
	 * @var string
	 */
	private $applicationKeyId;

	/**
	 * The secret part of the key. The master application key can also be used.
	 * @var string
	 */
	private $applicationKey;

	/**
	 * Application name, included in the User-Agent HTTP header.
	 * @var string
	 */
	private $applicationName;

	/**
	 * Custom Guzzle handler or handler stack.
	 * @var callable|\GuzzleHttp\HandlerStack
	 */
	private $handler;

	/**
	 * Custom Guzzle client instance.
	 * @var \GuzzleHttp\ClientInterface
	 */
	//private $http;

	/**
	 * Optional middleware to add to the GuzzleHttp handler stack.
	 * @var array
	 */
	public $middleware;

	/** @var bool */
	public $useHttpErrors;

	/**
	 * Number of times to retry an API call before throwing an exception.
	 * @var int
	 */
	public $maxRetries;

	/**
	 * Maximum amount of time, in seconds, to wait before retrying a failed request.
	 * Ignored for HTTP status codes: 429, 503.
	 * Should be a power of 2.
	 * 
	 * @var int
	 */
	public $maxRetryDelay;

	/**
	 * Download files with Server-Side Encryption headers instead of using query parameters.
	 * @var false
	 */
	public $useSSEHeaders;

	/**
	 * Maximum number of application keys to return per call.
	 * @var int
	 */
	private $maxKeyCount;

	/**
	 * Maximum number of files to return per call.
	 * @var int
	 */
	private $maxFileCount;

	/**
	 * Size limit to determine if the upload will use the large-file process.
	 * @var int
	 */
	public $largeFileUploadCustomMinimum; //200 * 1024 * 1024;

	/**
	 * An object that implements `AuthorizationCacheInterface` for caching
	 * account authorization between instances.
	 * 
	 * @var \Zaxbux\BackblazeB2\Interfaces\AuthorizationCacheInterface
	 */
	private $authorizationCache;

	/** @var \Zaxbux\BackblazeB2\Object\AccountAuthorization */
	//private $accountAuthorization;

	public function applicationKeyId(): string
	{
		return $this->applicationKeyId;
	}

	public function applicationKey(): string
	{
		return $this->applicationKey;
	}

	public function applicationName(): string
	{
		return $this->applicationName;
	}

	public function authorizationCache(): AuthorizationCacheInterface
	{
		return $this->authorizationCache;
	}

	public function maxKeyCount(): int
	{
		return $this->maxKeyCount;
	}

	public function maxFileCount(): int
	{
		return $this->maxFileCount;
	}

	public function __construct(
		string $applicationKeyId,
		string $applicationKey,
		?array $options = null
	) {
		$this->applicationKeyId = $applicationKeyId;
		$this->applicationKey = $applicationKey;
		$this->setOptions($options ?? []);
	}

	public function middleware(): array
	{
		return $this->middleware ?? [];
	}

	public function handler(): HandlerStack
	{
		if (!$this->handler) {
			$this->handler = HandlerStack::create();
		}

		if (is_callable($this->handler) && !$this->handler instanceof HandlerStack) {
			$this->handler = new HandlerStack(($this->handler));
		}

		return $this->handler;
	}

	public function maxRetries(): int
	{
		return $this->maxRetries;
	}

	public function maxRetryDelay(): int
	{
		return $this->maxRetryDelay();
	}

	/**
	 * 
	 * @param mixed $data 
	 * @return Config 
	 */
	public static function fromArray($data): Config
	{
		// Return existing instance
		if ($data instanceof Config) return $data;

		return new static(
			$data['applicationKeyId'] ?? $data[0],
			$data['applicationKey'] ?? $data[1],
			$data
		);
	}

	private function setOptions(array $options) {
		$options = array_merge(static::DEFAULTS, $options);

		$this->handler = $options['handler'];
		$this->maxRetries = $options['maxRetries'];
		$this->maxFileCount = $options['maxFileCount'];
		$this->maxKeyCount = $options['maxKeyCount'];
		$this->applicationName = $options['applicationName'];
		$this->authorizationCache = $options['authorizationCache'] ?? new BuiltinAuthorizationCache();
	}
}