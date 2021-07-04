<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2;

use Exception;

use GuzzleHttp\ClientInterface;
use Zaxbux\BackblazeB2\B2\Object\AccountAuthorization;
use Zaxbux\BackblazeB2\Class\IAuthorizationCache;
use Zaxbux\BackblazeB2\Client\Service\BucketService;
use Zaxbux\BackblazeB2\Client\Service\FileService;
use Zaxbux\BackblazeB2\Client\Service\KeyService;
use Zaxbux\BackblazeB2\Http\Config;
use Zaxbux\BackblazeB2\Http\ClientFactory;

/** @package Zaxbux\BackblazeB2 */
class Client
{
	public const B2_API_CLIENT_VERSION  = '2.0.0';
	public const B2_API_BASE_URL        = 'https://api.backblazeb2.com';
	public const B2_API_V2              = '/b2api/v2';

	/** @var KeyService */
	public $key;

	/** @var FileService */
	public $file;

	/** @var BucketService */
	public $bucket;

	/** @var ClientInterface */
	public $guzzle;

	/** @var string */
	protected $applicationKeyId;

	/** @var string */
	protected $applicationKey;

	/** @var AccountAuthorization */
	protected $accountAuthorization;

	/** @var IAuthorizationCache */
	protected $authorizationCache;

	/**
	 * Client constructor.
	 *
	 * @param string $applicationKeyId The identifier for the key. The account ID can also be used.
	 * @param string $applicationKey   The secret part of the key. The master application key can also be used.
	 * 
	 * @param IAuthorizationCache $authorizationCache [optional] An object implementing an authorization cache.
	 * @param ClientInterface     $client             [optional] A client compatible with `GuzzleHttp\ClientInterface`.
	 */
	public function __construct(
		string $applicationKeyId,
		string $applicationKey,
		?IAuthorizationCache $authorizationCache = null,
		?ClientInterface $guzzle = null
	) {
		$this->applicationKeyId   = $applicationKeyId;
		$this->applicationKey     = $applicationKey;
		$this->authorizationCache = $authorizationCache;

		$config = new Config();
		$config->client = $this;

		$this->guzzle = $guzzle ?: ClientFactory::create($config);

		$this->key = new KeyService($this);
		$this->file = new FileService($this);
		$this->bucket = new BucketService($this);
	}

	public function getApplicationKeyId(): string
	{
		return $this->getApplicationKeyId;
	}

	public function getApplicationKey(): string
	{
		return $this->getApplicationKey;
	}

	public function getAccountAuthorization(): ?AccountAuthorization
	{
		return $this->accountAuthorization;
	}

	public function setAccountAuthorization(AccountAuthorization $accountAuthorization): void
	{
		$this->accountAuthorization = $accountAuthorization;
	}

	/**
	 * Authorize the B2 account in order to get an auth token and API/download URLs.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_authorize_account.html
	 * 
	 * @throws \Exception
	 */
	protected function authorize()
	{
		// Try to fetch existing authorization token from cache.
		if ($this->authorizationCache instanceof IAuthorizationCache) {
			$this->accountAuthorization = $this->authorizationCache->get($this->applicationKeyId);
		}

		// Fetch a new authorization token from the API.
		if (!$this->accountAuthorization) {
			$this->accountAuthorization = AccountAuthorization::refresh($this->applicationKeyId, $this->applicationKey);

			// Cache the new authorization token.
			if ($this->authorizationCache instanceof IAuthorizationCache && !$this->accountAuthorization) {
				$this->authorizationCache->put($this->applicationKeyId, $this->accountAuthorization);
			}
		}

		if (empty($this->accountAuthorization)) {
			throw new \Exception('Failed to authorize account.');
		}
	}
}
