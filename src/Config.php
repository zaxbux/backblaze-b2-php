<?php

namespace Zaxbux\BackblazeB2;

use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use Zaxbux\BackblazeB2\Http\ClientFactory;
use Zaxbux\BackblazeB2\Interfaces\AuthorizationCacheInterface;
use Zaxbux\BackblazeB2\Object\AccountAuthorization;

class Config {

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
	 * Custom Guzzle handler or handler stack.
	 * @var callable|\GuzzleHttp\HandlerStack
	 */
	private $handler;

	/**
	 * Custom Guzzle client instance.
	 * @var \GuzzleHttp\ClientInterface
	 */
	private $client;

	/**
	 * Optional middleware to add to the GuzzleHttp handler stack.
	 * @var array
	 */
	public $middleware = [];

	/** @var bool */
	public $useHttpErrors = false;

	/**
	 * Number of times to retry an API call before throwing an exception.
	 * @var int
	 */
	public $maxRetries = 4;

	/**
	 * Maximum amount of time, in seconds, to wait before retrying a failed request.
	 * Ignored for HTTP status codes: 429, 503.
	 * Should be a power of 3.
	 * 
	 * @var int
	 */
	public $maxRetryDelay = 64;

	/**
	 * Download files with Server-Side Encryption headers instead of using query parameters.
	 * @var false
	 */
	public $useSSEHeaders = false;

	/**
	 * Maximum number of application keys to return per call.
	 * @var int
	 */
	public $maxKeyCount  = 1000;

	/**
	 * Maximum number of files to return per call.
	 * @var int
	 */
	public $maxFileCount = 1000;

	/**
	 * Size limit to determine if the upload will use the large-file process.
	 * @var int
	 */
	public $largeFileUploadCustomMinimum = null; //200 * 1024 * 1024;

		/**
	 * An object that implements `AuthorizationCacheInterface` for caching
	 * account authorization between instances.
	 * 
	 * @var \Zaxbux\BackblazeB2\Interfaces\AuthorizationCacheInterface
	 */
	private $authorizationCache;

	/** @var \Zaxbux\BackblazeB2\Object\AccountAuthorization */
	private $accountAuthorization;

	public function applicationKeyId(): string
	{
		return $this->applicationKeyId;
	}

	public function applicationKey(): string
	{
		return $this->applicationKey;
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

	public function client(): ClientInterface
	{
		if (!$this->client) {
			$this->client = ClientFactory::create($this);
		}

		return $this->client;
	}

	public function handler(): HandlerStack
	{
		if (!$this->handler) {
			$this->handler = new HandlerStack();
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
	 * @return AccountAuthorization 
	 * @throws Exception 
	 */
	public function accountAuthorization(): AccountAuthorization
	{
		// Check cache for account authorization if account is not already authorized.
		if (!$this->accountAuthorization && $this->authorizationCache instanceof AuthorizationCacheInterface) {
			$this->accountAuthorization = $this->authorizationCache->get($this->applicationKeyId);
		}

		// Refresh the token if it wasn't cached, or if it has expired.
		if (!$this->accountAuthorization || $this->accountAuthorization->expired()) {
			$this->accountAuthorization = Client::authorizeAccount(
				$this->applicationKeyId,
				$this->applicationKey,
				$this->client()
			);

			// Cache the new key
			if ($this->authorizationCache instanceof AuthorizationCacheInterface) {
				$this->authorizationCache->put($this->applicationKeyId, $this->accountAuthorization);
			}
		}

		// If account authorization still doesn't exist, there is an issue.
		if (!$this->accountAuthorization) {
			throw new Exception('Failed to get account authorization.');
		}

		return $this->accountAuthorization;
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
		$this->handler = $options['handler'] ?? null;
	}
}