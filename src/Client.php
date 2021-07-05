<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2;

use GuzzleHttp\ClientInterface;
use Zaxbux\BackblazeB2\Http\ClientFactory;
use Zaxbux\BackblazeB2\Config;
use Zaxbux\BackblazeB2\Interfaces\AuthorizationCacheInterface;
use Zaxbux\BackblazeB2\Object\AccountAuthorization;
use Zaxbux\BackblazeB2\Service\ApplicationKeyService;
use Zaxbux\BackblazeB2\Service\BucketService;
use Zaxbux\BackblazeB2\Service\FileService;
use Zaxbux\BackblazeB2\Traits\FileServiceHelpersTrait;

/** @package Zaxbux\BackblazeB2 */
class Client
{
	use FileService;
	use BucketService;
	use ApplicationKeyService;
	use FileServiceHelpersTrait {
		FileServiceHelpersTrait::deleteAllFileVersions as deleteAllFileVersions;
	}

	public const B2_API_CLIENT_VERSION  = '2.0.0';
	public const B2_API_BASE_URL        = 'https://api.backblazeb2.com';
	public const B2_API_V2              = '/b2api/v2';

	/** @var ApplicationKeyServiceInstance */
	//public $key;

	/** @var FileServiceInstance */
	//public $file;

	/** @var BucketServiceInstance */
	//public $bucket;

	/** @var ClientInterface */
	public $guzzle;

	/** @var string */
	protected $applicationKeyId;

	/** @var string */
	protected $applicationKey;

	/** @var AccountAuthorization */
	protected $accountAuthorization;

	/** @var AuthorizationCacheInterface */
	protected $authorizationCache;

	/**
	 * Client constructor.
	 *
	 * @param string $applicationKeyId The identifier for the key. The account ID can also be used.
	 * @param string $applicationKey   The secret part of the key. The master application key can also be used.
	 * 
	 * @param AuthorizationCacheInterface $authorizationCache [optional] An object implementing an authorization cache.
	 * @param ClientInterface     $client             [optional] A client compatible with `GuzzleHttp\ClientInterface`.
	 */
	public function __construct(
		string $applicationKeyId,
		string $applicationKey,
		?AuthorizationCacheInterface $authorizationCache = null,
		?array $options = []
	) {
		$this->applicationKeyId   = $applicationKeyId;
		$this->applicationKey     = $applicationKey;
		$this->authorizationCache = $authorizationCache;

		$config = new Config($this->getAccountAuthorization());

		$this->guzzle = $options['client'] ?? ClientFactory::create($config, $options['handler'] ?? null);
		$this->authorize();
	}

	public function getApplicationKeyId(): string
	{
		return $this->applicationKeyId;
	}

	public function getApplicationKey(): string
	{
		return $this->applicationKey;
	}

	public function getAccountAuthorization(): ?AccountAuthorization
	{
		if (!$this->accountAuthorization) {
			$this->accountAuthorization = new AccountAuthorization($this->applicationKeyId, $this->applicationKey);
		}

		return $this->accountAuthorization;
	}

	/*public function setAccountAuthorization(AccountAuthorization $accountAuthorization): void
	{
		//$this->accountAuthorization = $accountAuthorization;
	}*/

	/**
	 * Authorize the B2 account in order to get an auth token and API/download URLs.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_authorize_account.html
	 */
	public function authorize()
	{
		// Try to fetch existing authorization token from cache.
		if ($this->authorizationCache instanceof AuthorizationCacheInterface) {
			$this->accountAuthorization = $this->authorizationCache->get($this->applicationKeyId);
		}

		// Fetch a new authorization token from the API.
		if (!$this->accountAuthorization) {
			$this->accountAuthorization = $this->getAccountAuthorization();
		} //else {
			$this->accountAuthorization->refresh($this->guzzle);
		//}

		// Cache the new authorization token.
		if ($this->authorizationCache instanceof AuthorizationCacheInterface && $this->accountAuthorization) {
			$this->authorizationCache->put($this->applicationKeyId, $this->accountAuthorization);
		}
	}
}
