<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Object;

use Zaxbux\BackblazeB2\Interfaces\B2ObjectInterface;
use Zaxbux\BackblazeB2\Traits\HydrateFromResponseTrait;
use Zaxbux\BackblazeB2\Traits\ProxyArrayAccessToPropertiesTrait;

/** @package BackblazeB2\Object */
class Key implements B2ObjectInterface
{
	use ProxyArrayAccessToPropertiesTrait;
	use HydrateFromResponseTrait;

	public const ATTRIBUTE_ACCOUNT_ID               = 'accountId';
	public const ATTRIBUTE_APPLICATION_KEY_ID       = 'applicationKeyId';
	public const ATTRIBUTE_APPLICATION_KEY          = 'applicationKey';
	public const ATTRIBUTE_BUCKET_ID                = 'bucketId';
	public const ATTRIBUTE_CAPABILITIES             = 'capabilities';
	public const ATTRIBUTE_EXPIRATION_TIMESTAMP     = 'expirationTimestamp';
	public const ATTRIBUTE_KEY_NAME                 = 'name';
	public const ATTRIBUTE_MAX_KEY_COUNT            = 'maxKeyCount';
	public const ATTRIBUTE_NAME_PREFIX              = 'namePrefix';
	public const ATTRIBUTE_OPTIONS                  = 'options';
	public const ATTRIBUTE_START_APPLICATION_KEY_ID = 'startApplicationKeyId';
	public const ATTRIBUTE_VALID_DURATION           = 'validDurationInSeconds';

	/** @var string */
	private $accountId;

	/** @var string */
	private $bucketId;

	/** @var string */
	private $name;

	/** @var string */
	private $applicationKeyId;

	/** @var string */
	private $applicationKey;

	/** @var array */
	private $capabilities;

	/** @var int */
	private $expirationTimestamp;

	/** @var string */
	private $namePrefix;

	/** @var array */
	private $options;

	/**
	 * Get the value of accountId.
	 */ 
	public function getAccountId(): ?string
	{
		return $this->accountId;
	}

	/**
	 * Get the value of bucketId.
	 */ 
	public function getBucketId(): ?string
	{
		return $this->bucketId;
	}

	/**
	 * Get the value of name.
	 */ 
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * Get the value of applicationKey.
	 */ 
	public function getApplicationKey(): ?string
	{
		return $this->applicationKey;
	}

	/**
	 * Get the value of applicationKeyId.
	 */ 
	public function getApplicationKeyId(): string
	{
		return $this->applicationKeyId;
	}

	/**
	 * Get the value of capabilities.
	 */ 
	public function getCapabilities(): ?array
	{
		return $this->capabilities;
	}

	/**
	 * Get the value of expirationTimestamp.
	 */ 
	public function getExpirationTimestamp(): ?int
	{
		return $this->expirationTimestamp;
	}

	/**
	 * Get the value of namePrefix.
	 */ 
	public function getNamePrefix(): ?string
	{
		return $this->namePrefix;
	}

	/**
	 * Get the value of options.
	 */ 
	public function getOptions(): array
	{
		return $this->options;
	}

	public function __construct(
		string $applicationKeyId,
		?string $applicationKey = null,
		?string $name = null,
		?array $capabilities = null,
		?string $accountId = null,
		?int $expirationTimestamp = null,
		?string $bucketId = null,
		?string $namePrefix = null,
		?array $options = null
	) {
		$this->applicationKeyId = $applicationKeyId;
		$this->applicationKey = $applicationKey;
		$this->name = $name;
		$this->capabilities = $capabilities ?? [];
		$this->accountId = $accountId;
		$this->expirationTimestamp = $expirationTimestamp;
		$this->bucketId = $bucketId;
		$this->namePrefix = $namePrefix;
		$this->options = $options ?? [];
	}

	/**
	 * @inheritdoc
	 */
	public static function fromArray(array $data): Key {
		return new static(
			$data[static::ATTRIBUTE_KEY_NAME] ?? null,
			$data[static::ATTRIBUTE_APPLICATION_KEY_ID] ?? null,
			$data[static::ATTRIBUTE_APPLICATION_KEY] ?? null,
			$data[static::ATTRIBUTE_CAPABILITIES] ?? null,
			$data[static::ATTRIBUTE_ACCOUNT_ID] ?? null,
			$data[static::ATTRIBUTE_EXPIRATION_TIMESTAMP] ?? null,
			$data[static::ATTRIBUTE_BUCKET_ID] ?? null,
			$data[static::ATTRIBUTE_NAME_PREFIX] ?? null,
			$data[static::ATTRIBUTE_OPTIONS] ?? null
		);
	}

	/**
	 * @inheritdoc
	 */
	public function jsonSerialize(): array
	{
		return [
			static::ATTRIBUTE_KEY_NAME => $this->name,
			static::ATTRIBUTE_APPLICATION_KEY_ID => $this->applicationKeyId,
			static::ATTRIBUTE_CAPABILITIES => $this->capabilities,
			static::ATTRIBUTE_ACCOUNT_ID => $this->accountId,
			static::ATTRIBUTE_EXPIRATION_TIMESTAMP => $this->expirationTimestamp,
			static::ATTRIBUTE_BUCKET_ID => $this->bucketId,
			static::ATTRIBUTE_NAME_PREFIX => $this->namePrefix,
			static::ATTRIBUTE_OPTIONS => $this->options,
		];
	}
}