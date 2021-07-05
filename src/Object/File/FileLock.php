<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Object\File;

use ArrayAccess;
use JsonSerializable;
use Zaxbux\BackblazeB2\Object\File;
use Zaxbux\BackblazeB2\Traits\ProxyArrayAccessToPropertiesTrait;

/**
 * @link https://www.backblaze.com/b2/docs/server_side_encryption.html
 * @package Zaxbux\BackblazeB2\Object
 */
class FileLock implements JsonSerializable, ArrayAccess {
	use ProxyArrayAccessToPropertiesTrait;

	public const ATTRIBUTE_IS_CLIENT_AUTHORIZED_TO_READ = 'isClientAuthorizedToRead';
	public const ATTRIBUTE_RETENTION_MODE               = 'mode';
	public const ATTRIBUTE_RETENTION_PERIOD             = 'retainUntilTimestamp';
	public const ATTRIBUTE_LEGAL_HOLD                   = 'legalHold';

	public const HEADER_FILE_RETENTION_MODE   = 'X-Bz-File-Retention-Mode';
	public const HEADER_FILE_RETENTION_PERIOD = 'X-Bz-File-Retention-Retain-Until-Timestamp';
	public const HEADER_FILE_LEGAL_HOLD       = 'X-Bz-File-Legal-Hold';

	public const FILE_RETENTION_MODE_COMPLIANCE = 'compliance';
	public const FILE_RETENTION_MODE_GOVERNANCE = 'governance';

	public const LEGAL_HOLD_ENABLED  = 'on';
	public const LEGAL_HOLD_DISABLED = 'off';

	/** @var array */
	private $clientAuthorizedToRead = [];
	
	/** @var string */
	private $fileRetentionMode;
	
	/** @var int */
	private $fileRetentionPeriod;
	
	/** @var bool */
	private $legalHold;

	/**
	 * Get the value of fileRetentionMode
	 */ 
	public function getFileRetentionMode(): string
	{
		return $this->fileRetentionMode;
	}

	/**
	 * Set the value of fileRetentionMode
	 */ 
	public function setFileRetentionMode(string $fileRetentionMode): FileLock
	{
		$this->fileRetentionMode = $fileRetentionMode;

		return $this;
	}

	/**
	 * Get the value of fileRetentionPeriod
	 */ 
	public function getFileRetentionPeriod(): int
	{
		return $this->fileRetentionPeriod;
	}

	/**
	 * Set the value of fileRetentionPeriod
	 */ 
	public function setFileRetentionPeriod($fileRetentionPeriod): FileLock
	{
		$this->fileRetentionPeriod = $fileRetentionPeriod;

		return $this;
	}

	/**
	 * Get the value of legalHold
	 */ 
	public function getLegalHold(): string
	{
		return $this->legalHold ? static::LEGAL_HOLD_ENABLED : static::LEGAL_HOLD_DISABLED;
	}

	/**
	 * Set the value of legalHold
	 * @param bool|string $legalHold
	 */ 
	public function setLegalHold($legalHold): FileLock
	{
		if ($legalHold === static::LEGAL_HOLD_ENABLED || $legalHold === true) {
			$this->legalHold = static::LEGAL_HOLD_ENABLED;
		} else if ($legalHold === static::LEGAL_HOLD_DISABLED || $legalHold === false) {
			$this->legalHold = static::LEGAL_HOLD_DISABLED;
		}

		return $this;
	}

	public function __construct(
		?string $fileRetentionMode = null,
		?string $fileRetentionPeriod = null,
		?bool $legalHold = null,
		?array $clientAuthorizedToRead = []
	) {
		$this->fileRetentionMode = $fileRetentionMode;
		$this->fileRetentionPeriod = $fileRetentionPeriod;
		$this->legalHold = $legalHold === null ? null : (
			$legalHold ? static::LEGAL_HOLD_ENABLED : static::LEGAL_HOLD_DISABLED
		);
		$this->clientAuthorizedToRead = $clientAuthorizedToRead;
	}
	
	/**
	 * Get the File Lock configuration as headers understood by the B2 API.
	 * 
	 * @return array 
	 */
	public function getHeaders(): array
	{
		return array_filter([
			static::HEADER_FILE_RETENTION_MODE   => $this->fileRetentionMode,
			static::HEADER_FILE_RETENTION_PERIOD => $this->fileRetentionPeriod,
			static::HEADER_FILE_LEGAL_HOLD       => $this->legalHold,
		]);
	}

	/**
	 * @inheritdoc
	 */
	public static function fromArray(array $data): FileLock
	{
		return new static(
			$data[File::ATTRIBUTE_FILE_RETENTION][static::ATTRIBUTE_RETENTION_MODE] ?? $data[static::ATTRIBUTE_RETENTION_MODE] ?? null,
			$data[File::ATTRIBUTE_FILE_RETENTION][static::ATTRIBUTE_RETENTION_PERIOD] ?? $data[static::ATTRIBUTE_RETENTION_PERIOD] ?? null,
			$data[static::ATTRIBUTE_LEGAL_HOLD] ?? null,
			array_filter([
				$data[File::ATTRIBUTE_FILE_RETENTION][static::ATTRIBUTE_IS_CLIENT_AUTHORIZED_TO_READ] ?
					File::ATTRIBUTE_FILE_RETENTION : null,
				$data[File::ATTRIBUTE_LEGAL_HOLD][static::ATTRIBUTE_IS_CLIENT_AUTHORIZED_TO_READ] ?
					File::ATTRIBUTE_LEGAL_HOLD : null,
			])
		);
	}

	public function isClientAuthorizedToRead($attribute): ?bool {
		return $this->clientAuthorizedToRead[$attribute] ?? null;
	}

	public function toArray(): array
	{
		return [
			File::ATTRIBUTE_FILE_RETENTION => [
				static::ATTRIBUTE_IS_CLIENT_AUTHORIZED_TO_READ => null,
				'value' => $this->isClientAuthorizedToRead(File::ATTRIBUTE_LEGAL_HOLD) ? [
					static::ATTRIBUTE_RETENTION_MODE   => $this->fileRetentionMode,
					static::ATTRIBUTE_RETENTION_PERIOD => $this->fileRetentionPeriod,
				] : null,
			],
			File::ATTRIBUTE_LEGAL_HOLD => [
				static::ATTRIBUTE_IS_CLIENT_AUTHORIZED_TO_READ => null,
				'value' => $this->isClientAuthorizedToRead(File::ATTRIBUTE_LEGAL_HOLD) ?
					($this->legalHold ? static::LEGAL_HOLD_ENABLED : static::LEGAL_HOLD_DISABLED) : null,
			],
		];
	}

	/** @inheritdoc */
	public function jsonSerialize()
	{
		return $this->toArray();
	}
}
