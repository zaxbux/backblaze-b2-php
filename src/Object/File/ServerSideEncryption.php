<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Object\File;

use function md5;
use function base64_encode;
use function base64_decode;

use ArrayAccess;
use InvalidArgumentException;
use JsonSerializable;
use Zaxbux\BackblazeB2\Traits\ProxyArrayAccessToPropertiesTrait;

/**
 * @link https://www.backblaze.com/b2/docs/server_side_encryption.html
 * @package BackblazeB2\Object\File
 */
class ServerSideEncryption implements JsonSerializable, ArrayAccess
{
	use ProxyArrayAccessToPropertiesTrait;

	public const MODE_B2       = 'SSE-B2';
	public const MODE_CUSTOMER = 'SSE-C';

	public const ALGORITHM_AES256 = 'AES256';

	public const ATTRIBUTE_MODE             = 'mode';
	public const ATTRIBUTE_ALGORITHM        = 'algorithm';
	public const ATTRIBUTE_CUSTOMER_KEY     = 'customerKey';
	public const ATTRIBUTE_CUSTOMER_KEY_MD5 = 'customerKeyMd5';

	public const HEADER_SSE_CUSTOMER_ALGORITHM = 'X-Bz-Server-Side-Encryption-Customer-Algorithm';
	public const HEADER_SSE_CUSTOMER_KEY       = 'X-Bz-Server-Side-Encryption-Customer-Key';
	public const HEADER_SSE_CUSTOMER_KEY_MD5   = 'X-Bz-Server-Side-Encryption-Customer-Key-Md5';

	/** @var string */
	private $mode;

	/** @var string */
	private $algorithm;

	/** @var string */
	private $customerKey;

	/** @var string */
	private $customerKeyMd5;

	/**
	 * @param string $mode           `SSE-B2` for B2-managed SSE, or `SSE-C` for customer-managed SSE.
	 * @param string $algorithm      Only `AES256` is supported.
	 * @param string $customerKey    The base64-encoded encryption key.
	 * @param string $customerKeyMd5 The base64-encoded MD5 digest of the encryption key.
	 */
	public function __construct(
		?string $mode = null,
		?string $algorithm = null,
		?string $customerKey = null,
		?string $customerKeyMd5 = null
	) {
		$this->mode = $mode ?? static::MODE_B2;
		$this->algorithm = $algorithm ?? static::ALGORITHM_AES256;
		$this->customerKey = $customerKey;
		$this->customerKeyMd5 = $customerKeyMd5;
	}

	public static function fromCustomerKey(string $key, $raw = false): ServerSideEncryption
	{
		$digest = md5($raw ? $key : base64_decode($key));

		return new ServerSideEncryption(
			static::MODE_CUSTOMER,
			static::ALGORITHM_AES256,
			$raw ? base64_encode($key) : $key,
			base64_encode($digest)
		);
	}

	/**
	 * Get the value of mode.
	 */
	public function mode(): string
	{
		return $this->mode;
	}

	/**
	 * Set the value of mode.
	 * @param string $mode
	 * 
	 * @throws InvalidArgumentException If the `$mode` is not a valid mode.
	 */
	public function setMode(string $mode): ServerSideEncryption
	{
		/*if ($mode !== static::MODE_B2 || $mode !== static::MODE_CUSTOMER) {
			throw new InvalidArgumentException(
				'Argument $mode must be either "' . static::MODE_B2 . '" or "' . static::MODE_CUSTOMER . '".'
			);
		}*/

		$this->mode = $mode;

		return $this;
	}

	/**
	 * Get the value of algorithm.
	 */
	public function algorithm(): string
	{
		return $this->algorithm;
	}

	/**
	 * Set the value of algorithm.
	 * @param string $algorithm
	 */
	public function setAlgorithm(string $algorithm): ServerSideEncryption
	{
		$this->algorithm = $algorithm;

		return $this;
	}

	/**
	 * Get the value of customerKey.
	 * @param bool $raw Set to `true` to decode value as *base64* first.
	 */
	public function getCustomerKey(bool $raw = false): string
	{
		return $raw ? base64_decode($this->customerKey) : $this->customerKey;
	}

	/**
	 * Set the value of customerKey.
	 * @param string $customerKey AES-256 encryption key.
	 * @param bool   $raw         Set to `true` to encode value as *base64* first.
	 */
	public function setCustomerKey(string $customerKey, bool $raw = false): ServerSideEncryption
	{
		$this->customerKey = $raw ? base64_encode($customerKey) : $customerKey;

		return $this;
	}

	/**
	 * Get the value of customerKeyMd5.
	 * @param bool $raw Set to `true` to decode value as *base64* first.
	 */
	public function getCustomerKeyMd5(bool $raw = false): string
	{
		return $raw ? base64_decode($this->customerKeyMd5) : $this->customerKeyMd5;
	}

	/**
	 * Set the value of customerKeyMd5.
	 * @param string $customerKeyMd5 MD5 digest of the encryption key.
	 * @param bool   $raw            Set to `true` to encode value as *base64* first.
	 */
	public function setCustomerKeyMd5(string $customerKeyMd5, bool $raw = false): ServerSideEncryption
	{
		$this->customerKeyMd5 = $raw ? base64_encode($customerKeyMd5) : $customerKeyMd5;

		return $this;
	}

	/**
	 * Get the SSE configuration as headers understood by the B2 API.
	 * 
	 * @return array 
	 */
	public function getHeaders(): array
	{
		if ($this->mode === static::MODE_B2) {
			return [];
		}

		return [
			static::HEADER_SSE_CUSTOMER_ALGORITHM => $this->algorithm,
			static::HEADER_SSE_CUSTOMER_KEY       => $this->customerKey,
			static::HEADER_SSE_CUSTOMER_KEY_MD5   => $this->customerKeyMd5,
		];
	}

	/**
	 * @inheritdoc
	 * 
	 * @param bool $rawKeys Set to `true` to encode keys as *base64* first.
	 * @return ServerSideEncryption 
	 */
	public static function fromArray(array $data, bool $rawKeys = false): ServerSideEncryption
	{
		$customerKey    = $data[static::ATTRIBUTE_CUSTOMER_KEY] ?? null;
		$customerKeyMd5 = $data[static::ATTRIBUTE_CUSTOMER_KEY_MD5] ?? null;

		return new ServerSideEncryption(
			$data[static::ATTRIBUTE_MODE] ?? null,
			$data[static::ATTRIBUTE_ALGORITHM] ?? null,
			$customerKey ? ($rawKeys ? base64_encode($customerKey) : $customerKey) : null,
			$customerKeyMd5 ? ($rawKeys ? base64_encode($customerKeyMd5) : $customerKeyMd5) : null
		);
	}

	public function toArray(): array
	{
		return [
			static::ATTRIBUTE_MODE => $this->mode ?? static::MODE_CUSTOMER,
			static::ATTRIBUTE_ALGORITHM => $this->algorithm ?? static::ALGORITHM_AES256,
			static::ATTRIBUTE_CUSTOMER_KEY => $this->customerKey,
			static::ATTRIBUTE_CUSTOMER_KEY_MD5 => $this->customerKeyMd5,
		];
	}

	/** @inheritdoc */
	public function jsonSerialize(): mixed
	{
		return $this->toArray();
	}
}
