<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Object\File;

use function is_array;
use function array_filter;
use function http_build_query;

use ArrayAccess;
use JsonSerializable;
use Zaxbux\BackblazeB2\Object\File\ServerSideEncryption;
use Zaxbux\BackblazeB2\Traits\ProxyArrayAccessToPropertiesTrait;

/** @package BackblazeB2\Object\File */
final class DownloadOptions implements ArrayAccess, JsonSerializable
{
	use ProxyArrayAccessToPropertiesTrait;

	public const OPTION_AUTHORIZATION       = 'authorization';
	public const OPTION_CONTENT_DISPOSITION = 'b2ContentDisposition';
	public const OPTION_CONTENT_ENCODING    = 'b2ContentEncoding';
	public const OPTION_CONTENT_LANGUAGE    = 'b2ContentLanguage';
	public const OPTION_CONTENT_TYPE        = 'b2ContentType';
	public const OPTION_CACHE_CONTROL       = 'b2CacheControl';
	public const OPTION_EXPIRES             = 'b2Expires';
	public const OPTION_RANGE               = 'range';
	public const OPTION_SSE                 = 'serverSideEncryption';

	public const HEADER_RANGE                  = 'Range';
	public const HEADER_AUTHORIZATION          = 'Authorization';

	/** @var string */
	private $authorization;

	/** @var string */
	private $contentDisposition;

	/** @var string */
	private $contentEncoding;

	/** @var string */
	private $contentLanguage;

	/** @var string */
	private $contentType;

	/** @var string */
	private $cacheControl;

	/** @var string */
	private $expires;

	/** @var string */
	private $range;

	/** @var ServerSideEncryption|array */
	private $serverSideEncryption;

	/** @var bool */
	private $sseHeadersEnabled = false;
	
	/**
	 * 
	 * @param string                     $authorization 
	 * @param string                     $contentDisposition 
	 * @param string                     $contentEncoding 
	 * @param string                     $contentLanguage 
	 * @param string                     $contentType 
	 * @param string                     $cacheControl 
	 * @param string                     $expires 
	 * @param string                     $range 
	 * @param ServerSideEncryption|array $serverSideEncryption 
	 * @return void 
	 */
	public function __construct(
		?string $authorization = null,
		?string $contentDisposition = null,
		?string $contentEncoding = null,
		?string $contentLanguage = null,
		?string $contentType = null,
		?string $cacheControl = null,
		?string $expires = null,
		?string $range = null,
		$serverSideEncryption = null
	) {
		$this->authorization = $authorization;
		$this->contentDisposition = $contentDisposition;
		$this->contentEncoding = $contentEncoding;
		$this->contentLanguage = $contentLanguage;
		$this->contentType = $contentType;
		$this->cacheControl = $cacheControl;
		$this->expires = $expires;
		$this->range = $range;
		
		if (! $serverSideEncryption instanceof ServerSideEncryption) {
			$serverSideEncryption = ServerSideEncryption::fromArray($serverSideEncryption ?? []);
		}

		$this->serverSideEncryption = $serverSideEncryption;
	}

	/*******************\
	|  Getters/Setters  |
	\*******************/

	/**
	 * Get the value of contentDisposition.
	 */ 
	public function contentDisposition(): ?string
	{
		return $this->contentDisposition;
	}

	/**
	 * Set the value of contentDisposition.
	 *
	 * @param string $contentDisposition
	 */ 
	public function setContentDisposition(string $contentDisposition): DownloadOptions
	{
		$this->contentDisposition = $contentDisposition;

		return $this;
	}

	/**
	 * Get the value of contentEncoding.
	 */ 
	public function contentEncoding(): ?string
	{
		return $this->contentEncoding;
	}

	/**
	 * Set the value of contentEncoding.
	 *
	 * @param string $contentEncoding
	 */ 
	public function setContentEncoding(string $contentEncoding): DownloadOptions
	{
		$this->contentEncoding = $contentEncoding;

		return $this;
	}

	/**
	 * Get the value of contentLanguage.
	 */ 
	public function contentLanguage(): ?string
	{
		return $this->contentLanguage;
	}

	/**
	 * Set the value of contentLanguage.
	 *
	 * @param string $contentLanguage
	 */ 
	public function setContentLanguage(string $contentLanguage): DownloadOptions
	{
		$this->contentLanguage = $contentLanguage;

		return $this;
	}

	/**
	 * Get the value of contentType.
	 */ 
	public function contentType(): ?string
	{
		return $this->contentType;
	}

	/**
	 * Set the value of contentType.
	 *
	 * @param string $contentType
	 */ 
	public function setContentType(string $contentType): DownloadOptions
	{
		$this->contentType = $contentType;

		return $this;
	}

	/**
	 * Get the value of cacheControl.
	 */ 
	public function cacheControl(): ?string
	{
		return $this->cacheControl;
	}

	/**
	 * Set the value of cacheControl.
	 *
	 * @param string $cacheControl
	 */ 
	public function setCacheControl(string $cacheControl): DownloadOptions
	{
		$this->cacheControl = $cacheControl;

		return $this;
	}

	/**
	 * Get the value of expires.
	 */ 
	public function expires(): ?string
	{
		return $this->expires;
	}

	/**
	 * Set the value of expires.
	 *
	 * @param string $expires
	 */ 
	public function setExpires(string $expires): DownloadOptions
	{
		$this->expires = $expires;

		return $this;
	}

	/**
	 * Get the value of range.
	 */ 
	public function range(): string
	{
		return $this->range;
	}

	/**
	 * Set the value of range.
	 * 
	 * @param string $range A standard RFC 7233 byte-range request string,
	 *                      which will return just part of the stored file.
	 */ 
	public function setRange($range): DownloadOptions
	{
		$this->range = $range;

		return $this;
	}

	/**
	 * Get the value of serverSideEncryption.
	 */ 
	public function serverSideEncryption(): ServerSideEncryption
	{
		return $this->serverSideEncryption;
	}

	/**
	 * Set the value of serverSideEncryption.
	 *
	 * @param ServerSideEncryption|array|null
	 */ 
	public function setServerSideEncryption($serverSideEncryption): DownloadOptions
	{
		$this->serverSideEncryption = $serverSideEncryption instanceof ServerSideEncryption
			? $serverSideEncryption
			: ServerSideEncryption::fromArray($serverSideEncryption);

		return $this;
	}

	/******************\
	|  Public methods  |
	\******************/

	/**
	 * Use Server-Side Encryption headers instead of query parameters in download requests.
	 * 
	 * @param bool $value To only use headers, pass `true`. Pass `false` to use query parameters.
	 */
	public function useSSEHeaders(?bool $value = true): void
	{
		$this->sseHeadersEnabled = $value;
	}

	/**
	 * Return array of options for `b2_get_download_authorization`.
	 * 
	 * @return array 
	 */
	public function authorizationOptions(): array
	{
		return array_filter([
			static::OPTION_CONTENT_DISPOSITION => $this->contentDisposition,
			static::OPTION_CONTENT_ENCODING => $this->contentEncoding,
			static::OPTION_CONTENT_LANGUAGE => $this->contentLanguage,
			static::OPTION_CONTENT_TYPE => $this->contentType,
			static::OPTION_CACHE_CONTROL => $this->cacheControl,
			static::OPTION_EXPIRES => $this->expires,
		]);
	}

	/**
	 * Return an array of HTTP headers that represents the current download options.
	 * 
	 * @return array Array of headers for the B2 API.
	 */
	public function getHeaders(): array
	{
		return array_filter([
			static::HEADER_RANGE => $this->range,
			static::HEADER_AUTHORIZATION => $this->authorization,
		] + (
			$this->sseHeadersEnabled ? $this->serverSideEncryption->getHeaders() : []
		));
	}

	/**
	 * 
	 * @return string[] 
	 */
	public function toArray() {
		return array_filter([
			static::OPTION_AUTHORIZATION => $this->authorization,
			static::OPTION_CONTENT_DISPOSITION => $this->contentDisposition,
			static::OPTION_CONTENT_ENCODING => $this->contentEncoding,
			static::OPTION_CONTENT_LANGUAGE => $this->contentLanguage,
			static::OPTION_CONTENT_TYPE => $this->contentType,
			static::OPTION_CACHE_CONTROL => $this->cacheControl,
			static::OPTION_EXPIRES => $this->expires,
			static::OPTION_RANGE => $this->range,
			static::OPTION_SSE => $this->serverSideEncryption->toArray(),
		]);
	}

	/**
	 * Build a query string of parameters for `b2_download_file_by_*`.
	 * 
	 * @return string The query string.
	 */
	public function toQueryString() {
		return http_build_query([
			static::OPTION_AUTHORIZATION => $this->authorization,
			static::OPTION_CONTENT_DISPOSITION => $this->contentDisposition,
			static::OPTION_CONTENT_ENCODING => $this->contentEncoding,
			static::OPTION_CONTENT_LANGUAGE => $this->contentLanguage,
			static::OPTION_CONTENT_TYPE => $this->contentType,
			static::OPTION_CACHE_CONTROL => $this->cacheControl,
			static::OPTION_EXPIRES => $this->expires,
			static::OPTION_SSE => $this->serverSideEncryption,
		]);
	}

	/**
	 * @inheritdoc
	 */
	public static function fromArray(array $data)
	{
		return new DownloadOptions(
			$data[static::OPTION_AUTHORIZATION] ?? null,
			$data[static::OPTION_CONTENT_DISPOSITION] ?? null,
			$data[static::OPTION_CONTENT_ENCODING] ?? null,
			$data[static::OPTION_CONTENT_LANGUAGE] ?? null,
			$data[static::OPTION_CONTENT_TYPE] ?? null,
			$data[static::OPTION_CACHE_CONTROL] ?? null,
			$data[static::OPTION_EXPIRES] ?? null,
			$data[static::OPTION_RANGE] ?? null,
			$data[static::OPTION_SSE] ?? null
		);
	}

	/**
	 * @inheritdoc
	 */
	public function jsonSerialize()
	{
		return $this->toArray();
	}
}
