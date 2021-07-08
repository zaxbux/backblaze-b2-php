<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Object;

use Zaxbux\BackblazeB2\Object\File\FileActionType;
use Zaxbux\BackblazeB2\Interfaces\B2ObjectInterface;
use Zaxbux\BackblazeB2\Classes\FilePathInfo;
use Zaxbux\BackblazeB2\Object\File\FileInfo;
use Zaxbux\BackblazeB2\Object\File\ServerSideEncryption;
use Zaxbux\BackblazeB2\Traits\HydrateFromResponseTrait;
use Zaxbux\BackblazeB2\Traits\ProxyArrayAccessToPropertiesTrait;


/** @package BackblazeB2\Object */
class File implements B2ObjectInterface
{
	use ProxyArrayAccessToPropertiesTrait;
	use HydrateFromResponseTrait;

	public const ATTRIBUTE_ACCOUNT_ID            = 'accountId';
	public const ATTRIBUTE_ACTION                = 'action';
	public const ATTRIBUTE_BUCKET_ID             = 'bucketId';
	public const ATTRIBUTE_BYPASS_GOVERNANCE     = 'bypassGovernance';
	public const ATTRIBUTE_CONTENT_LENGTH        = 'contentLength';
	public const ATTRIBUTE_CONTENT_MD5           = 'contentMd5';
	public const ATTRIBUTE_CONTENT_SHA1          = 'contentSha1';
	public const ATTRIBUTE_CONTENT_TYPE          = 'contentType';
	public const ATTRIBUTE_DELIMITER             = 'delimiter';
	public const ATTRIBUTE_DESTINATION_BUCKET_ID = 'destinationBucketId';
	public const ATTRIBUTE_DESTINATION_SSE       = 'destinationServerSideEncryption';
	public const ATTRIBUTE_FILE_ID               = 'fileId';
	public const ATTRIBUTE_FILE_INFO             = 'fileInfo';
	public const ATTRIBUTE_FILE_NAME             = 'fileName';
	public const ATTRIBUTE_FILE_NAME_PREFIX      = 'fileNamePrefix';
	public const ATTRIBUTE_FILE_RETENTION        = 'fileRetention';
	public const ATTRIBUTE_LARGE_FILE_ID         = 'largeFileId';
	public const ATTRIBUTE_LEGAL_HOLD            = 'legalHold';
	public const ATTRIBUTE_MAX_FILE_COUNT        = 'maxFileCount';
	public const ATTRIBUTE_MAX_PART_COUNT        = 'maxPartCount';
	public const ATTRIBUTE_METADATA_DIRECTIVE    = 'metadataDirective';
	public const ATTRIBUTE_NAME_PREFIX           = 'namePrefix';
	public const ATTRIBUTE_PART_NUMBER           = 'partNumber';
	public const ATTRIBUTE_PART_SHA1_ARRAY       = 'partSha1Array';
	public const ATTRIBUTE_PREFIX                = 'prefix';
	public const ATTRIBUTE_RANGE                 = 'range';
	public const ATTRIBUTE_SOURCE_FILE_ID        = 'sourceFileId';
	public const ATTRIBUTE_SOURCE_SSE            = 'sourceServerSideEncryption';
	public const ATTRIBUTE_SSE                   = 'serverSideEncryption';
	public const ATTRIBUTE_START_FILE_ID         = 'startFileId';
	public const ATTRIBUTE_START_FILE_NAME       = 'startFileName';
	public const ATTRIBUTE_START_PART_NUMBER     = 'startPartNumber';
	public const ATTRIBUTE_UPLOAD_TIMESTAMP      = 'uploadTimestamp';
	public const ATTRIBUTE_VALID_DURATION        = 'validDurationInSeconds';

	public const CONTENT_TYPE_AUTO = 'b2/x-auto';

	public const METADATA_DIRECTIVE_COPY    = 'COPY';
	public const METADATA_DIRECTIVE_REPLACE = 'REPLACE';

	public const HEADER_X_BZ_CONTENT_SHA1 = 'X-Bz-Content-Sha1';
	public const HEADER_X_BZ_FILE_NAME    = 'X-Bz-File-Name';
	public const HEADER_X_BZ_PART_NUMBER  = 'X-Bz-Part-Number';

	// 0 bytes
	public const SINGLE_FILE_MIN_SIZE = 0;

	// 5 GiB
	public const SINGLE_FILE_MAX_SIZE = 5368709120;

	// 100 MiB
	public const LARGE_FILE_MIN_SIZE = 104857600;

	// 10 TiB
	public const LARGE_FILE_MAX_SIZE = 5368709120;

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

	/** @var FileInfo */
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

	/** @var ServerSideEncryption */
	private $serverSideEncryption;

	/** @var int */
	private $partNumber;

	/**
	 * @param string $id 
	 * @param string $name 
	 * @param string $bucketId 
	 * @param string $action 
	 * @param array|FileInfo  $fileInfo 
	 * @param int    $contentLength 
	 * @param string $contentType 
	 * @param string $contentMd5 
	 * @param string $contentSha1 
	 * @param int    $uploadTimestamp 
	 * @param string $accountId 
	 * @param array  $retention 
	 * @param string|array  $legalHold 
	 * @param array  $serverSideEncryption 
	 * @param int    $partNumber 
	 */
	public function __construct(
		string $id,
		?string $name = null,
		?string $bucketId = null,
		?string $action = null,
		$fileInfo = null,
		?int $contentLength = null,
		?string $contentType = null,
		?string $contentMd5 = null,
		?string $contentSha1 = null,
		?int $uploadTimestamp = null,
		?string $accountId = null,
		?array $retention = null,
		$legalHold = null,
		?array $serverSideEncryption = null,
		?int $partNumber = null
	) {
		$this->id                   = $id;
		$this->name                 = $name;
		$this->bucketId             = $bucketId;
		$this->action               = $action ?
			FileActionType::fromString($action) : null;
		$this->info             = $fileInfo instanceof FileInfo ?
			$fileInfo : FileInfo::fromArray($fileInfo ?? []);
		$this->contentLength        = $contentLength;
		$this->contentType          = $contentType;
		$this->contentMd5           = $contentMd5;
		$this->contentSha1          = $contentSha1;
		$this->uploadTimestamp      = $uploadTimestamp;
		$this->accountId            = $accountId;
		$this->retention            = $retention;
		$this->legalHold            = is_array($legalHold) ? $legalHold : ['value' => $legalHold];
		$this->serverSideEncryption = $serverSideEncryption instanceof ServerSideEncryption ?
			$serverSideEncryption : ServerSideEncryption::fromArray($serverSideEncryption ?? []);
		$this->partNumber           = $partNumber;
	}

	/**
	 * Get the file account ID.
	 */
	public function accountId(): string
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
	public function id(): string
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
	public function name(): string
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
	public function bucketId(): string
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
	public function action(): FileActionType
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
	public function info(): FileInfo
	{
		return $this->info;
	}

	/**
	 * Set the file info.
	 * 
	 * @param array|FileInfo $info
	 */
	public function setInfo($info): File
	{
		$this->info = $info;

		return $this;
	}

	/**
	 * Get the file size.
	 */
	public function contentLength(): int
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
	public function contentType(): string
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
	public function contentSha1(): string
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
	public function contentMd5(): string
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
	public function uploadTimestamp(): int
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
	public function retention(): array
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
	public function legalHold(): array
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
	public function serverSideEncryption(): ?ServerSideEncryption
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
	public function partNumber(): ?int
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
	 * Get the timestamp of when the file was last modified, if available.
	 * 
	 * @param null|bool $milliseconds Set to `false` to get timestamp in seconds.
	 * @return null|int Time since UNIX epoch.
	 */
	public function lastModifiedTimestamp(?bool $milliseconds = true): ?int
	{
		if ($this->info) {
			$t = $this->info->get(FileInfo::B2_FILE_INFO_MTIME, null);
			return $milliseconds ? $t : round($t / 1000);
		}

		return null;
	}

	/**
	 * @see pathinfo()
	 */
	public function pathInfo(): FilePathInfo
	{
		return FilePathInfo::fromPath($this->name);
	}

	public static function fromArray(array $data): File
	{
		return new File(
			$data[static::ATTRIBUTE_FILE_ID],
			$data[static::ATTRIBUTE_FILE_NAME] ?? null,
			$data[static::ATTRIBUTE_BUCKET_ID] ?? null,
			$data[static::ATTRIBUTE_ACTION] ?? null,
			$data[static::ATTRIBUTE_FILE_INFO] ?? null,
			$data[static::ATTRIBUTE_CONTENT_LENGTH] ?? null,
			$data[static::ATTRIBUTE_CONTENT_TYPE] ?? null,
			$data[static::ATTRIBUTE_CONTENT_SHA1] ?? null,
			$data[static::ATTRIBUTE_CONTENT_MD5] ?? null,
			(int) ($data[static::ATTRIBUTE_UPLOAD_TIMESTAMP] ?? null),
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
			static::ATTRIBUTE_FILE_INFO        => $this->info,
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
