<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\B2\Object;

use Zaxbux\BackblazeB2\Classes\B2ObjectBase;
use Zaxbux\BackblazeB2\Traits\ProxyArrayAccessToProperties;

class Key implements B2ObjectBase
{
	use ProxyArrayAccessToProperties;

	public const ATTRIBUTE_ACCOUNT_ID               = 'accountId';
	public const ATTRIBUTE_APPLICATION_KEY_ID       = 'applicationKeyId';
	public const ATTRIBUTE_BUCKET_ID                = 'bucketId';
	public const ATTRIBUTE_CAPABILITIES             = 'capabilities';
	public const ATTRIBUTE_EXPIRATION_TIMESTAMP     = 'expirationTimestamp';
	public const ATTRIBUTE_KEY_NAME                 = 'keyName';
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
	private $keyName;

	/** @var string */
	private $applicationKeyId;

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
	public function getAccountId(): string
	{
		return $this->accountId;
	}

	/**
	 * Set the value of accountId.
	 */ 
	public function setAccountId($accountId): Key
	{
		$this->accountId = $accountId;

		return $this;
	}

	/**
	 * Get the value of bucketId.
	 */ 
	public function getBucketId(): string
	{
		return $this->bucketId;
	}

	/**
	 * Set the value of bucketId.
	 */ 
	public function setBucketId($bucketId): Key
	{
		$this->bucketId = $bucketId;

		return $this;
	}

	/**
	 * Get the value of keyName.
	 */ 
	public function getKeyName(): string
	{
		return $this->keyName;
	}

	/**
	 * Set the value of keyName.
	 */ 
	public function setKeyName($keyName): Key
	{
		$this->keyName = $keyName;

		return $this;
	}

	/**
	 * Get the value of applicationKeyId.
	 */ 
	public function getApplicationKeyId(): string
	{
		return $this->applicationKeyId;
	}

	/**
	 * Set the value of applicationKeyId.
	 */ 
	public function setApplicationKeyId(string $applicationKeyId): Key
	{
		$this->applicationKeyId = $applicationKeyId;

		return $this;
	}

	/**
	 * Get the value of capabilities.
	 */ 
	public function getCapabilities(): array
	{
		return $this->capabilities;
	}

	/**
	 * Set the value of capabilities.
	 */ 
	public function setCapabilities(array $capabilities): Key
	{
		$this->capabilities = $capabilities;

		return $this;
	}

	/**
	 * Get the value of expirationTimestamp.
	 */ 
	public function getExpirationTimestamp(): int
	{
		return $this->expirationTimestamp;
	}

	/**
	 * Set the value of expirationTimestamp.
	 */ 
	public function setExpirationTimestamp(int $expirationTimestamp): Key
	{
		$this->expirationTimestamp = $expirationTimestamp;

		return $this;
	}

	/**
	 * Get the value of namePrefix.
	 */ 
	public function getNamePrefix(): string
	{
		return $this->namePrefix;
	}

	/**
	 * Set the value of namePrefix.
	 */ 
	public function setNamePrefix(string $namePrefix): Key
	{
		$this->namePrefix = $namePrefix;

		return $this;
	}

	/**
	 * Get the value of options.
	 */ 
	public function getOptions(): array
	{
		return $this->options;
	}

	/**
	 * Set the value of options.
	 */ 
	public function setOptions(array $options): Key
	{
		$this->options = $options;

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public static function fromArray(array $data): Key {
		return new Key(
			$data[static::ATTRIBUTE_ACCOUNT_ID] ?? null,
			$data[static::ATTRIBUTE_BUCKET_ID] ?? null,
			$data[static::ATTRIBUTE_KEY_NAME] ?? null,
			$data[static::ATTRIBUTE_APPLICATION_KEY_ID] ?? null,
			$data[static::ATTRIBUTE_CAPABILITIES] ?? null,
			$data[static::ATTRIBUTE_EXPIRATION_TIMESTAMP] ?? null,
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
			static::ATTRIBUTE_ACCOUNT_ID => $this->accountId,
			static::ATTRIBUTE_BUCKET_ID => $this->bucketId,
			static::ATTRIBUTE_KEY_NAME => $this->keyName,
			static::ATTRIBUTE_APPLICATION_KEY_ID => $this->applicationKeyId,
			static::ATTRIBUTE_CAPABILITIES => $this->capabilities,
			static::ATTRIBUTE_EXPIRATION_TIMESTAMP => $this->expirationTimestamp,
			static::ATTRIBUTE_NAME_PREFIX => $this->namePrefix,
			static::ATTRIBUTE_OPTIONS => $this->options,
		];
	}
}