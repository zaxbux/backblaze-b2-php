<?php

namespace Zaxbux\BackblazeB2\B2Object;

use Zaxbux\BackblazeB2\B2\Type\FileActionType;
use Zaxbux\BackblazeB2\Class\B2ObjectBase;
use Zaxbux\BackblazeB2\Class\FilePathInfo;
use Zaxbux\BackblazeB2\Class\IterableFromArrayTrait;
use Zaxbux\BackblazeB2\Class\ProxyArrayAccessToProperties;

/** @package Zaxbux\BackblazeB2 */
class File implements B2ObjectBase
{
	use ProxyArrayAccessToProperties;
	use IterableFromArrayTrait;

	public const ATTRIBUTE_ACTION           = 'action';
	public const ATTRIBUTE_CONTENT_LENGTH   = 'contentLength';
	public const ATTRIBUTE_CONTENT_SHA1     = 'contentSha1';
	public const ATTRIBUTE_CONTENT_MD5      = 'contentMd5';
	public const ATTRIBUTE_CONTENT_TYPE     = 'contentType';
	public const ATTRIBUTE_FILE_ID          = 'fileId';
	public const ATTRIBUTE_FILE_INFO        = 'fileInfo';
	public const ATTRIBUTE_FILE_NAME        = 'fileName';
	public const ATTRIBUTE_FILE_RETENTION   = 'fileRetention';
	public const ATTRIBUTE_LEGAL_HOLD       = 'legalHold';
	public const ATTRIBUTE_SSE              = 'serverSideEncryption';
	public const ATTRIBUTE_UPLOAD_TIMESTAMP = 'uploadTimestamp';
	public const ATTRIBUTE_PART_NUMBER      = 'partNumber';

	public const CONTENT_TYPE_AUTO = 'b2/x-auto';

	public const METADATA_DIRECTIVE_COPY    = 'COPY';
	public const METADATA_DIRECTIVE_REPLACE = 'REPLACE';

	/** @var string */
	private $accountId;

	/** @var string */
	private $id;

	/** @var string */
	private $name;

	/** @var string */
	private $bucketId;

	/** @var FileActionType */
	private $action;

	/** @var array */
	private $info;

	/** @var int */
	private $contentLength;

	/** @var string */
	private $contentType;

	/** @var string */
	private $contentSha1;

	/** @var string */
	private $contentMd5;

	/** @var int */
	private $uploadTimestamp;

	/** @var array */
	private $retention;

	/** @var array */
	private $legalHold;

	/** @var array */
	private $serverSideEncryption;

	/** @var int */
	private $partNumber;

	/**
	 * @param string $id 
	 * @param string $name 
	 * @param string $bucketId 
	 * @param null|string $action 
	 * @param null|array $fileInfo 
	 * @param null|int $contentLength 
	 * @param null|string $contentType 
	 * @param null|string $contentMd5 
	 * @param null|string $contentSha1 
	 * @param null|string $accountId 
	 * @param null|array $retention 
	 * @param null|array $legalHold 
	 * @param null|int $partNumber 
	 */
	public function __construct(
		string $id,
		string $name,
		?string $bucketId = null,
		?string $action = null,
		?array $fileInfo = null,
		?int $contentLength = null,
		?string $contentType = null,
		?string $contentMd5 = null,
		?string $contentSha1 = null,
		?string $accountId = null,
		?array $retention = null,
		?array $legalHold = null,
		?int $partNumber = null,
	) {
		$this->id            = $id;
		$this->name          = $name;
		$this->bucketId      = $bucketId;
		$this->action        = FileActionType::fromString($action);
		$this->fileInfo      = $fileInfo;
		$this->contentLength = $contentLength;
		$this->contentType   = $contentType;
		$this->contentMd5    = $contentMd5;
		$this->contentSha1   = $contentSha1;
		$this->accountId     = $accountId;
		$this->retention     = $retention;
		$this->legalHold     = $legalHold;
		$this->partNumber    = $partNumber;
	}

	/**
	 * Get the file account ID.
	 */
	public function getAccountId(): string
	{
		return $this->accountId;
	}

	/**
	 * Set the file account ID.
	 *
	 * @param string $accountId
	 */
	public function setAccountId(string $accountId): File
	{
		$this->accountId = $accountId;

		return $this;
	}

	/**
	 * Get the file ID.
	 */
	public function getId(): string
	{
		return $this->id;
	}

	/**
	 * Set the file ID.
	 * 
	 * @param string $id
	 */
	public function setId(string $id): File
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * Get the file name (path).
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Set the file name (path).
	 */
	public function setName($name): File
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * Get the file bucket ID.
	 */
	public function getBucketId(): string
	{
		return $this->bucketId;
	}

	/**
	 * Set the file bucket ID.
	 *
	 * @return  self
	 */
	public function setBucketId($bucketId)
	{
		$this->bucketId = $bucketId;

		return $this;
	}

	/**
	 * Get the file action (type).
	 */
	public function getAction(): FileActionType
	{
		return $this->action;
	}

	/**
	 * Set the file action.
	 * 
	 * @param FileActionType|string $action
	 */
	public function setAction(string $action): File
	{
		$this->action = FileActionType::fromString($action);

		return $this;
	}

	/**
	 * Get the file info.
	 */
	public function getInfo(): array
	{
		return $this->info;
	}

	/**
	 * Set the file info.
	 * 
	 * @param array $info
	 */
	public function setInfo(array $info): FIle
	{
		$this->info = $info;

		return $this;
	}

	/**
	 * Get the file size.
	 */
	public function getContentLength(): int
	{
		return $this->contentLength;
	}

	/**
	 * Set the file size.
	 * 
	 * @param int $contentLength
	 */
	public function setContentLength(int $contentLength): File
	{
		$this->contentLength = $contentLength;

		return $this;
	}

	/**
	 * Get the file type.
	 */
	public function getContentType(): string
	{
		return $this->contentType;
	}

	/**
	 * Set the file type.
	 * 
	 * @param string $mimeType 
	 */
	public function setContentType(string $contentType): File
	{
		$this->contentType = $contentType;

		return $this;
	}

	/**
	 * Get the file SHA1 checksum.
	 */
	public function getContentSha1(): string
	{
		return $this->contentSha1;
	}

	/**
	 * Set the file SHA1 checksum.
	 * 
	 * @param string $contentSha1
	 */
	public function setContentSha1(string $contentSha1): File
	{
		$this->contentSha1 = $contentSha1;

		return $this;
	}

	/**
	 * Get the file MD5 hash.
	 */
	public function getContentMd5(): string
	{
		return $this->contentMd5;
	}

	/**
	 * Set the file MD5 hash.
	 * 
	 * @param string $contentMd5
	 */
	public function setContentMd5(string $contentMd5): File
	{
		$this->contentMd5 = $contentMd5;

		return $this;
	}

	/**
	 * Get the UTC timestamp when the file was uploaded. Always `0` if the action is `folder`.
	 */
	public function getUploadTimestamp(): int
	{
		return $this->action->isFolder() ? 0 : $this->uploadTimestamp;
	}

	/**
	 * Set the UTC timestamp when the file was uploaded. Will always be `0` if the action is `folder`.
	 */
	public function setUploadTimestamp(int $uploadTimestamp): File
	{
		$this->uploadTimestamp = $this->action->isFolder() ? 0 : $uploadTimestamp;

		return $this;
	}

	/**
	 * Get the value of retention.
	 */
	public function getRetention(): array
	{
		return $this->retention;
	}

	/**
	 * Set the value of retention.
	 * 
	 * @param array $retention
	 */
	public function setRetention(string $retention): File
	{
		$this->retention = $retention;

		return $this;
	}

	/**
	 * Get the value of legalHold.
	 */
	public function getLegalHold(): array
	{
		return $this->legalHold;
	}

	/**
	 * Set the value of legalHold.
	 *
	 * @param array $legalHold
	 */
	public function setLegalHold($legalHold): File
	{
		$this->legalHold = $legalHold;

		return $this;
	}

	/**
	 * Get the value of serverSideEncryption.
	 */
	public function getServerSideEncryption(): array
	{
		return $this->serverSideEncryption;
	}

	/**
	 * Set the value of serverSideEncryption.
	 *
	 * @param array $serverSideEncryption
	 */
	public function setServerSideEncryption($serverSideEncryption): File
	{
		$this->serverSideEncryption = $serverSideEncryption;

		return $this;
	}

	/**
	 * Get the value of partNumber.
	 */
	public function getPartNumber(): int
	{
		return $this->partNumber;
	}

	/**
	 * Set the value of partNumber.
	 *
	 * @param int $partNumber
	 */
	public function setPartNumber($partNumber): File
	{
		$this->partNumber = $partNumber;

		return $this;
	}

	/**
	 * @see pathinfo()
	 */
	public function getPathInfo(): FilePathInfo
	{
		return FilePathInfo::fromPath($this->name);
	}

	public static function fromArray(array $data): File
	{
		return new File(
			$data[static::ATTRIBUTE_FILE_ID],
			$data[static::ATTRIBUTE_FILE_NAME],
			$data[static::ATTRIBUTE_BUCKET_ID] ?? null,
			$data[static::ATTRIBUTE_ACTION] ?? null,
			$data[static::ATTRIBUTE_FILE_INFO] ?? null,
			$data[static::ATTRIBUTE_CONTENT_LENGTH] ?? null,
			$data[static::ATTRIBUTE_CONTENT_TYPE] ?? null,
			$data[static::ATTRIBUTE_CONTENT_SHA1] ?? null,
			$data[static::ATTRIBUTE_CONTENT_MD5] ?? null,
			$data[static::ATTRIBUTE_UPLOAD_TIMESTAMP] ?? null,
			$data[static::ATTRIBUTE_ACCOUNT_ID] ?? null,
			$data[static::ATTRIBUTE_FILE_RETENTION] ?? null,
			$data[static::ATTRIBUTE_LEGAL_HOLD] ?? null,
			$data[static::ATTRIBUTE_SSE] ?? null,
			$data[static::ATTRIBUTE_PART_NUMBER] ?? null,
		);
	}

	/**
	 * @inheritdoc
	 */
	public function jsonSerialize(): array
	{
		return [
			static::ATTRIBUTE_FILE_ID          => $this->id,
			static::ATTRIBUTE_FILE_NAME        => $this->name,
			static::ATTRIBUTE_BUCKET_ID        => $this->bucketId,
			static::ATTRIBUTE_ACTION           => $this->action,
			static::ATTRIBUTE_FILE_INFO        => $this->fileInfo,
			static::ATTRIBUTE_CONTENT_LENGTH   => $this->contentLength,
			static::ATTRIBUTE_CONTENT_TYPE     => $this->contentType,
			static::ATTRIBUTE_CONTENT_SHA1     => $this->contentSha1,
			static::ATTRIBUTE_CONTENT_MD5      => $this->contentMd5,
			static::ATTRIBUTE_UPLOAD_TIMESTAMP => $this->uploadTimestamp,
			static::ATTRIBUTE_ACCOUNT_ID       => $this->accountId,
			static::ATTRIBUTE_FILE_RETENTION   => $this->retention,
			static::ATTRIBUTE_LEGAL_HOLD       => $this->legalHold,
			static::ATTRIBUTE_SSE              => $this->serverSideEncryption,
			static::ATTRIBUTE_PART_NUMBER      => $this->partNumber,
		];
	}
}
