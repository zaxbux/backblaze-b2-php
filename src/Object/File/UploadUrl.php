<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Object\File;

use Zaxbux\BackblazeB2\Traits\HydrateFromResponseTrait;

/** @package BackblazeB2\Object\File */
class UploadUrl {

	use HydrateFromResponseTrait;
	
	public const ATTRIBUTE_BUCKET_ID           = 'bucketId';
	public const ATTRIBUTE_UPLOAD_URL          = 'uploadUrl';
	public const ATTRIBUTE_AUTHORIZATION_TOKEN = 'authorizationToken';

	/** @var string */
	private $bucketId;
	
	/** @var string */
	private $uploadUrl;
	
	/** @var string */
	private $authorizationToken;

	public function __construct(
		string $bucketId,
		string $uploadUrl,
		string $authorizationToken
	) {
		$this->bucketId           = $bucketId;
		$this->uploadUrl          = $uploadUrl;
		$this->authorizationToken = $authorizationToken;
	}

	/**
	 * Get the value of bucketId.
	 */ 
	public function bucketId(): string
	{
		return $this->bucketId;
	}

	/**
	 * Get the value of uploadUrl.
	 */ 
	public function uploadUrl(): string
	{
		return $this->uploadUrl;
	}

	/**
	 * Get the value of authorizationToken.
	 */ 
	public function authorizationToken(): string
	{
		return $this->authorizationToken;
	}

	/** @inheritdoc */
	public static function fromArray(array $data): UploadUrl
	{
		return new UploadUrl(
			$data[static::ATTRIBUTE_BUCKET_ID],
			$data[static::ATTRIBUTE_UPLOAD_URL],
			$data[static::ATTRIBUTE_AUTHORIZATION_TOKEN],
		);
	}
}