<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Object;

use function time;

use GuzzleHttp\Utils;
use Psr\Http\Message\ResponseInterface;
use Zaxbux\BackblazeB2\Interfaces\B2ObjectInterface;
use Zaxbux\BackblazeB2\Interfaces\AuthorizationCacheInterface;
use Zaxbux\BackblazeB2\Traits\HydrateFromResponseTrait;
use Zaxbux\BackblazeB2\Traits\ProxyArrayAccessToPropertiesTrait;

/** @package BackblazeB2\Object */
class AccountAuthorization implements B2ObjectInterface
{
	use ProxyArrayAccessToPropertiesTrait;
	use HydrateFromResponseTrait;

	public const ATTRIBUTE_ABSOLUTE_MINIMUM_PART_SIZE = 'absoluteMinimumPartSize';
	public const ATTRIBUTE_ACCOUNT_ID                 = 'accountId';
	public const ATTRIBUTE_ALLOWED                    = 'allowed';
	public const ATTRIBUTE_API_URL                    = 'apiUrl';
	public const ATTRIBUTE_APPLICATION_KEY            = 'applicationKey';
	public const ATTRIBUTE_APPLICATION_KEY_ID         = 'applicationKeyId';
	public const ATTRIBUTE_CREATED                    = '_created';
	public const ATTRIBUTE_AUTHORIZATION_TOKEN        = 'authorizationToken';
	public const ATTRIBUTE_DOWNLOAD_URL               = 'downloadUrl';
	public const ATTRIBUTE_RECOMMENDED_PART_SIZE      = 'recommendedPartSize';
	public const ATTRIBUTE_S3_API_URL                 = 's3ApiUrl';

	/**  @deprecated Removed in B2 API v2 */
	public const ATTRIBUTE_MINIMUM_PART_SIZE          = 'minimumPartSize';

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
	private $created;

	public function __construct(
		?string $accountId = null,
		?string $authorizationToken = null,
		?array $allowed = null,
		?string $apiUrl = null,
		?string $downloadUrl = null,
		?int $recommendedPartSize = null,
		?int $absoluteMinimumPartSize = null,
		?string $s3ApiUrl = null,
		?int $created = -1
	) {
		$this->accountId               = $accountId;
		$this->authorizationToken      = $authorizationToken;
		$this->allowed                 = $allowed;
		$this->apiUrl                  = $apiUrl;
		$this->downloadUrl             = $downloadUrl;
		$this->recommendedPartSize     = $recommendedPartSize;
		$this->absoluteMinimumPartSize = $absoluteMinimumPartSize;
		$this->s3ApiUrl                = $s3ApiUrl;
		$this->created                 = $created ?? time();
	}

	/**
	 * Get the value of accountId
	 */
	public function accountId(): ?string
	{
		return $this->accountId;
	}

	/**
	 * Get the value of authorizationToken
	 */
	public function authorizationToken(): ?string
	{
		return $this->authorizationToken;
	}

	/**
	 * Get the capabilities, bucket restrictions, and prefix restrictions.
	 * 
	 * @return null|string|array
	 */
	public function allowed(?string $key = null)
	{
		if ($key) {
			return $this->allowed[$key] ?? null;
		}

		return $this->allowed;
	}

	/**
	 * Get the value of apiUrl
	 */
	public function apiUrl(): ?string
	{
		return $this->apiUrl;
	}

	/**
	 * Get the value of downloadUrl
	 */
	public function downloadUrl(): ?string
	{
		return $this->downloadUrl;
	}

	/**
	 * The recommended part size for each part of a large file (except the last one).
	 * It is recommended to use this part size for optimal performance.
	 * 
	 * @return int The recomended part size in bytes.
	 */
	public function recommendedPartSize(): ?int
	{
		return $this->recommendedPartSize;
	}

	/**
	 * The smallest possible size of a part of a large file (except the last one).
	 * Upload performance may be impacted if you use this value.
	 * 
	 * @return int The absolute minimum part size in bytes.
	 */
	public function absoluteMinimumPartSize(): ?int
	{
		return $this->absoluteMinimumPartSize;
	}

	/**
	 * Get the value of s3ApiUrl
	 */
	public function s3ApiUrl(): ?string
	{
		return $this->s3ApiUrl;
	}

	/**
	 * Check if the authorization token has expired, based on the `created`.
	 * Will always return `false` if there is no `created`.
	 * 
	 * @return bool `true` if `NOW` - `VALIDITY_PERIOD` ≥ `AUTHORIZATION_TIMESTAMP`; `false` otherwise.
	 */
	public function expired(): bool
	{
		return time() - AuthorizationCacheInterface::EXPIRES >= $this->created;
	}

	public function hasCapability(string $capability): bool
	{
		return in_array($capability, $this->allowed['capabilities'] ?? []);
	}

	public static function fromArray(array $data): AccountAuthorization
	{
		return new static(
			$data[static::ATTRIBUTE_ACCOUNT_ID] ?? null,
			$data[static::ATTRIBUTE_AUTHORIZATION_TOKEN] ?? null,
			$data[static::ATTRIBUTE_ALLOWED] ?? null,
			$data[static::ATTRIBUTE_API_URL] ?? null,
			$data[static::ATTRIBUTE_DOWNLOAD_URL] ?? null,
			$data[static::ATTRIBUTE_RECOMMENDED_PART_SIZE] ?? null,
			$data[static::ATTRIBUTE_ABSOLUTE_MINIMUM_PART_SIZE] ?? null,
			$data[static::ATTRIBUTE_S3_API_URL] ?? null,
			$data[static::ATTRIBUTE_CREATED] ?? null
		);
	}

	public function jsonSerialize(): array
	{
		return [
			static::ATTRIBUTE_ACCOUNT_ID => $this->accountId,
			static::ATTRIBUTE_AUTHORIZATION_TOKEN => $this->authorizationToken,
			static::ATTRIBUTE_ALLOWED => $this->allowed,
			static::ATTRIBUTE_API_URL => $this->apiUrl,
			static::ATTRIBUTE_DOWNLOAD_URL => $this->downloadUrl,
			static::ATTRIBUTE_RECOMMENDED_PART_SIZE => $this->recommendedPartSize,
			static::ATTRIBUTE_ABSOLUTE_MINIMUM_PART_SIZE => $this->absoluteMinimumPartSize,
			static::ATTRIBUTE_S3_API_URL => $this->s3ApiUrl,
			static::ATTRIBUTE_CREATED => $this->created,
		];
	}

	public function __toString()
	{
		return json_encode($this);
	}
}
