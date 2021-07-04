<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\B2\Object;

use function time;
use function json_encode;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use Zaxbux\BackblazeB2\Classes\B2ObjectBase;
use Zaxbux\BackblazeB2\Classes\IAuthorizationCache;
use Zaxbux\BackblazeB2\Traits\ProxyArrayAccessToProperties;

/** @package Zaxbux\BackblazeB2\Client */
class AccountAuthorization implements B2ObjectBase
{
	use ProxyArrayAccessToProperties;

	public const ATTRIBUTE_ABSOLUTE_MINIMUM_PART_SIZE = 'absoluteMinimumPartSize';
	public const ATTRIBUTE_ACCOUNT_ID                 = 'accountId';
	public const ATTRIBUTE_ALLOWED                    = 'allowed';
	public const ATTRIBUTE_API_URL                    = 'apiUrl';
	public const ATTRIBUTE_APPLICATION_KEY            = 'applicationKey';
	public const ATTRIBUTE_APPLICATION_KEY_ID         = 'applicationKeyId';
	public const ATTRIBUTE_AUTHORIZATION_TIMESTAMP    = 'authorizationTimestamp';
	public const ATTRIBUTE_AUTHORIZATION_TOKEN        = 'authorizationToken';
	public const ATTRIBUTE_DOWNLOAD_URL               = 'downloadUrl';
	public const ATTRIBUTE_RECOMMENDED_PART_SIZE      = 'recommendedPartSize';
	public const ATTRIBUTE_S3_API_URL                 = 's3ApiUrl';

	/**  @deprecated */
	public const ATTRIBUTE_MINIMUM_PART_SIZE          = 'minimumPartSize';

	/** @var string */
	private $applicationKeyId;

	/** @var string */
	private $applicationKey;

	/** @var string */
	private $accountId;

	/** @var string */
	private $authorizationToken;

	/** @var array */
	private $allowed;

	/** @var string */
	private $apiUrl;

	/** @var string */
	private $downloadUrl;

	/** @var int */
	private $recommendedPartSize;

	/** @var int */
	private $absoluteMinimumPartSize;

	/** @var string */
	private $s3ApiUrl;

	/** @var int */
	private $authorizationTimestamp;

	public function __construct(
		string $applicationKeyId,
		string $applicationKey,
		?string $accountId = null,
		?string $authorizationToken = null,
		?array $allowed = null,
		?string $apiUrl = null,
		?string $downloadUrl = null,
		?int $recommendedPartSize = null,
		?int $absoluteMinimumPartSize = null,
		?string $s3ApiUrl = null,
		?int $authorizationTimestamp = -1
	) {
		$this->applicationKey = $applicationKey;
		$this->applicationKeyId = $applicationKeyId;
		$this->accountId = $accountId;
		$this->authorizationToken = $authorizationToken;
		$this->allowed = $allowed;
		$this->apiUrl = $apiUrl;
		$this->downloadUrl = $downloadUrl;
		$this->recommendedPartSize = $recommendedPartSize;
		$this->absoluteMinimumPartSize = $absoluteMinimumPartSize;
		$this->s3ApiUrl = $s3ApiUrl;
		$this->authorizationTimestamp = $authorizationTimestamp;
	}

	/**
	 * Get the value of applicationKeyId
	 */ 
	public function getApplicationKeyId(): string
	{
		return $this->applicationKeyId;
	}

	/**
	 * Get the value of applicationKey
	 */ 
	public function getApplicationKey(): string
	{
		return $this->applicationKey;
	}

	/**
	 * Get the value of accountId
	 */
	public function getAccountId(): ?string
	{
		return $this->accountId;
	}

	/**
	 * Get the value of authorizationToken
	 */
	public function getAuthorizationToken(): ?string
	{
		return $this->authorizationToken;
	}

	/**
	 * Get the capabilities, bucket restrictions, and prefix restrictions.
	 */
	public function getAllowed(): ?array
	{
		return $this->allowed;
	}

	/**
	 * Get the value of apiUrl
	 */
	public function getApiUrl(): ?string
	{
		return $this->apiUrl;
	}

	/**
	 * Get the value of downloadUrl
	 */
	public function getDownloadUrl(): ?string
	{
		return $this->downloadUrl;
	}

	/**
	 * The recommended part size for each part of a large file (except the last one).
	 * It is recommended to use this part size for optimal performance.
	 * 
	 * @return int The recomended part size in bytes.
	 */
	public function getRecommendedPartSize(): ?int
	{
		return $this->recommendedPartSize;
	}

	/**
	 * The smallest possible size of a part of a large file (except the last one).
	 * Upload performance may be impacted if you use this value.
	 * 
	 * @return int The absolute minimum part size in bytes.
	 */
	public function getAbsoluteMinimumPartSize(): ?int
	{
		return $this->absoluteMinimumPartSize;
	}

	/**
	 * Get the value of s3ApiUrl
	 */
	public function getS3ApiUrl(): ?string
	{
		return $this->s3ApiUrl;
	}

	/**
	 * Get the value of authorizationTimestamp
	 */ 
	public function getAuthorizationTimestamp(): int
	{
		return $this->authorizationTimestamp;
	}

	/**
	 * Check if the authorization token has expired, based on the `authorizationTimestamp`.
	 * Will always return `false` if there is no `authorizationTimestamp`.
	 * 
	 * @return bool `true` if `NOW` - `VALIDITY_PERIOD` ≥ `AUTHORIZATION_TIMESTAMP`; `false` otherwise.
	 */
	public function isStale(): bool
	{
		return time() - IAuthorizationCache::EXPIRES >= $this->authorizationTimestamp ?? -1;
	}

	public function refresh(ClientInterface $client): void {
		$response = $client->request('GET', '/b2_authorize_account', [
			'headers' => [
				'Authorization' => static::getBasicAuthorization($this->applicationKeyId, $this->applicationKey),
			],
		]);

		$data = json_decode((string) $response->getBody(), true);

		$this->accountId               = $data[static::ATTRIBUTE_ACCOUNT_ID];
		$this->authorizationToken      = $data[static::ATTRIBUTE_AUTHORIZATION_TOKEN];
		$this->allowed                 = $data[static::ATTRIBUTE_ALLOWED];
		$this->apiUrl                  = $data[static::ATTRIBUTE_API_URL];
		$this->downloadUrl             = $data[static::ATTRIBUTE_DOWNLOAD_URL];
		$this->recommendedPartSize     = $data[static::ATTRIBUTE_RECOMMENDED_PART_SIZE];
		$this->absoluteMinimumPartSize = $data[static::ATTRIBUTE_ABSOLUTE_MINIMUM_PART_SIZE];
		$this->s3ApiUrl                = $data[static::ATTRIBUTE_S3_API_URL];
		$this->authorizationTimestamp  = time();
	}

	public static function fromArray(array $data): AccountAuthorization
	{
		return new AccountAuthorization(
			$data[static::ATTRIBUTE_APPLICATION_KEY_ID],
			$data[static::ATTRIBUTE_APPLICATION_KEY],
			$data[static::ATTRIBUTE_ACCOUNT_ID] ?? null,
			$data[static::ATTRIBUTE_AUTHORIZATION_TOKEN] ?? null,
			$data[static::ATTRIBUTE_ALLOWED] ?? null,
			$data[static::ATTRIBUTE_API_URL] ?? null,
			$data[static::ATTRIBUTE_DOWNLOAD_URL] ?? null,
			$data[static::ATTRIBUTE_RECOMMENDED_PART_SIZE] ?? null,
			$data[static::ATTRIBUTE_ABSOLUTE_MINIMUM_PART_SIZE] ?? null,
			$data[static::ATTRIBUTE_S3_API_URL] ?? null,
			$data[static::ATTRIBUTE_AUTHORIZATION_TIMESTAMP] ?? -1
		);
	}

	public function jsonSerialize(): array
	{
		return [
			static::ATTRIBUTE_APPLICATION_KEY_ID => $this->applicationKeyId,
			static::ATTRIBUTE_APPLICATION_KEY => $this->applicationKey,
			static::ATTRIBUTE_ACCOUNT_ID => $this->accountId,
			static::ATTRIBUTE_AUTHORIZATION_TOKEN => $this->authorizationToken,
			static::ATTRIBUTE_ALLOWED => $this->allowed,
			static::ATTRIBUTE_API_URL => $this->apiUrl,
			static::ATTRIBUTE_DOWNLOAD_URL => $this->downloadUrl,
			static::ATTRIBUTE_RECOMMENDED_PART_SIZE => $this->recommendedPartSize,
			static::ATTRIBUTE_ABSOLUTE_MINIMUM_PART_SIZE => $this->absoluteMinimumPartSize,
			static::ATTRIBUTE_S3_API_URL => $this->s3ApiUrl,
			static::ATTRIBUTE_AUTHORIZATION_TIMESTAMP => $this->authorizationTimestamp,
		];
	}

	public function __toString()
	{
		return json_encode($this);
	}

	/**
	 * Create a base64 encoded string comprised of the application key and application key ID.
	 * 
	 * @internal
	 * 
	 * @param string $applicationKeyId 
	 * @param string $applicationKey   
	 * 
	 * @return string base64 encoded string for HTTP `Authorization` header.
	 */
	private static function getBasicAuthorization(string $applicationKeyId, string $applicationKey): string {
		return 'Basic ' . base64_encode($applicationKeyId . ':' . $applicationKey);
	}
}
