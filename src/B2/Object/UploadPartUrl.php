<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\B2\Object;

class UploadPartUrl {
	public const ATTRIBUTE_FILE_ID             = 'fileId';
	public const ATTRIBUTE_UPLOAD_URL          = 'uploadUrl';
	public const ATTRIBUTE_AUTHORIZATION_TOKEN = 'authorizationToken';

	/** @var string */
	private $fileId;
	
	/** @var string */
	private $uploadUrl;
	
	/** @var string */
	private $authorizationToken;

	public function __construct(
		string $fileId,
		string $uploadUrl,
		string $authorizationToken
	) {
		$this->fileId = $fileId;
		$this->uploadUrl = $uploadUrl;
		$this->authorizationToken = $authorizationToken;
	}

	/**
	 * Get the value of fileId.
	 */ 
	public function getFileId(): string
	{
		return $this->fileId;
	}

	/**
	 * Get the value of uploadUrl.
	 */ 
	public function getUploadUrl(): string
	{
		return $this->uploadUrl;
	}

	/**
	 * Get the value of authorizationToken.
	 */ 
	public function getAuthorizationToken(): string
	{
		return $this->authorizationToken;
	}

	/** @inheritdoc */
	public static function fromArray(array $data): UploadPartUrl
	{
		return new UploadPartUrl(
			$data[static::ATTRIBUTE_FILE_ID],
			$data[static::ATTRIBUTE_UPLOAD_URL],
			$data[static::ATTRIBUTE_AUTHORIZATION_TOKEN],
		);
	}
}