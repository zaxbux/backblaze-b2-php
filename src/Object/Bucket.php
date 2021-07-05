<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Object;

use Zaxbux\BackblazeB2\Interfaces\B2ObjectInterface;
use Zaxbux\BackblazeB2\Object\Bucket\BucketInfo;
//use Zaxbux\BackblazeB2\Traits\IterableFromArrayTrait;
use Zaxbux\BackblazeB2\Traits\ProxyArrayAccessToPropertiesTrait;

class Bucket implements B2ObjectInterface
{
	use ProxyArrayAccessToPropertiesTrait;
	//use IterableFromArrayTrait;

	public const ATTRIBUTE_ACCOUNT_ID       = 'accountId';
	public const ATTRIBUTE_BUCKET_ID        = 'bucketId';
	public const ATTRIBUTE_BUCKET_INFO      = 'bucketInfo';
	public const ATTRIBUTE_BUCKET_NAME      = 'bucketName';
	public const ATTRIBUTE_BUCKET_TYPE      = 'bucketType';
	public const ATTRIBUTE_BUCKET_TYPES     = 'bucketTypes';
	public const ATTRIBUTE_BUCKETS          = 'buckets';
	public const ATTRIBUTE_CORS_RULES       = 'corsRules';
	public const ATTRIBUTE_DEFAULT_SSE      = 'defaultServerSideEncryption';
	public const ATTRIBUTE_FILE_LOCK_CONFIG = 'fileLockConfiguration';
	public const ATTRIBUTE_IF_REVISION_IS   = 'ifRevisionIs';
	public const ATTRIBUTE_LIFECYCLE_RULES  = 'lifecycleRules';
	public const ATTRIBUTE_OPTIONS          = 'options';
	public const ATTRIBUTE_REVISION         = 'revision';


	/** @var string */
	private $accountId;

	/** @var string */
	private $id;

	/** @var string */
	private $name;

	/** @var string */
	private $type;

	/** @var array */
	private $info;

	/** @var array */
	private $corsRules;

	/** @var array */
	private $defaultServerSideEncryption;

	/** @var array */
	private $fileLockConfiguration;

	/** @var array */
	private $lifecycleRules;

	/** @var int */
	private $revision;

	/** @var string */
	private $options;

	public function __construct(
		string $id,
		string $name,
		string $type,
		$info = null,
		?string $accountId = null,
		?array $corsRules = null,
		?array $defaultServerSideEncryption = null,
		?array $fileLockConfiguration = null,
		?array $lifecycleRules = null,
		?int $revision = null,
		?array $options = null
	) {
		$this->id                          = $id;
		$this->name                        = $name;
		$this->type                        = $type;
		$this->info                        = $info instanceof BucketInfo ?
			$info : BucketInfo::fromArray($info ?? []);
		$this->accountId                   = $accountId;
		$this->corsRules                   = $corsRules;
		$this->defaultServerSideEncryption = $defaultServerSideEncryption;
		$this->fileLockConfiguration       = $fileLockConfiguration;
		$this->lifecycleRules              = $lifecycleRules;
		$this->revision                    = $revision;
		$this->options                     = $options;
	}

	/**
	 * Get the value of accountId
	 */
	public function getAccountId()
	{
		return $this->accountId;
	}

	/**
	 * Set the value of accountId
	 *
	 * @return  self
	 */
	public function setAccountId($accountId)
	{
		$this->accountId = $accountId;

		return $this;
	}

	/**
	 * Get the value of id
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set the value of id
	 *
	 * @return  self
	 */
	public function setId($id)
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * Get the value of name
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set the value of name
	 *
	 * @return  self
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * Get the value of type
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Set the value of type
	 *
	 * @return  self
	 */
	public function setType($type)
	{
		$this->type = $type;

		return $this;
	}

	/**
	 * Get the value of info
	 */
	public function getInfo()
	{
		return $this->info;
	}

	/**
	 * Set the value of info
	 *
	 * @return  self
	 */
	public function setInfo($info)
	{
		$this->info = $info;

		return $this;
	}

	/**
	 * Get the value of corsRules
	 */
	public function getCorsRules()
	{
		return $this->corsRules;
	}

	/**
	 * Set the value of corsRules
	 *
	 * @return  self
	 */
	public function setCorsRules($corsRules)
	{
		$this->corsRules = $corsRules;

		return $this;
	}

	/**
	 * Get the value of fileLockConfiguration
	 */
	public function getFileLockConfiguration()
	{
		return $this->fileLockConfiguration;
	}

	/**
	 * Set the value of fileLockConfiguration
	 *
	 * @return  self
	 */
	public function setFileLockConfiguration($fileLockConfiguration)
	{
		$this->fileLockConfiguration = $fileLockConfiguration;

		return $this;
	}

	/**
	 * Get the value of defaultServerSideEncryption
	 */
	public function getDefaultServerSideEncryption()
	{
		return $this->defaultServerSideEncryption;
	}

	/**
	 * Set the value of defaultServerSideEncryption
	 *
	 * @return  self
	 */
	public function setDefaultServerSideEncryption($defaultServerSideEncryption)
	{
		$this->defaultServerSideEncryption = $defaultServerSideEncryption;

		return $this;
	}

	/**
	 * Get the value of lifecycleRules
	 */
	public function getLifecycleRules()
	{
		return $this->lifecycleRules;
	}

	/**
	 * Set the value of lifecycleRules
	 *
	 * @return  self
	 */
	public function setLifecycleRules($lifecycleRules)
	{
		$this->lifecycleRules = $lifecycleRules;

		return $this;
	}

	/**
	 * Get the value of revision
	 */
	public function getRevision()
	{
		return $this->revision;
	}

	/**
	 * Set the value of revision
	 *
	 * @return  self
	 */
	public function setRevision($revision)
	{
		$this->revision = $revision;

		return $this;
	}

	/**
	 * Get the value of options
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * Set the value of options
	 *
	 * @return  self
	 */
	public function setOptions($options)
	{
		$this->options = $options;

		return $this;
	}

	public static function fromArray(array $data): Bucket
	{
		return new Bucket(
			$data[static::ATTRIBUTE_BUCKET_ID],
			$data[static::ATTRIBUTE_BUCKET_NAME],
			$data[static::ATTRIBUTE_BUCKET_TYPE],
			$data[static::ATTRIBUTE_BUCKET_INFO] ?? null,
			$data[static::ATTRIBUTE_ACCOUNT_ID] ?? null,
			$data[static::ATTRIBUTE_CORS_RULES] ?? null,
			$data[static::ATTRIBUTE_DEFAULT_SSE] ?? null,
			$data[static::ATTRIBUTE_FILE_LOCK_CONFIG] ?? null,
			$data[static::ATTRIBUTE_LIFECYCLE_RULES] ?? null,
			$data[static::ATTRIBUTE_REVISION] ?? null,
			$data[static::ATTRIBUTE_OPTIONS] ?? null,
		);
	}

	/**
	 * @inheritdoc
	 */
	public function jsonSerialize(): array
	{
		return [
			static::ATTRIBUTE_BUCKET_ID        => $this->id,
			static::ATTRIBUTE_BUCKET_NAME      => $this->name,
			static::ATTRIBUTE_BUCKET_TYPE      => $this->type,
			static::ATTRIBUTE_BUCKET_INFO      => $this->info,
			static::ATTRIBUTE_ACCOUNT_ID       => $this->accountId,
			static::ATTRIBUTE_CORS_RULES       => $this->corsRules,
			static::ATTRIBUTE_DEFAULT_SSE      => $this->defaultServerSideEncryption,
			static::ATTRIBUTE_FILE_LOCK_CONFIG => $this->fileLockConfiguration,
			static::ATTRIBUTE_LIFECYCLE_RULES  => $this->lifecycleRules,
			static::ATTRIBUTE_REVISION         => $this->revision,
			static::ATTRIBUTE_OPTIONS          => $this->options,
		];
	}
}
