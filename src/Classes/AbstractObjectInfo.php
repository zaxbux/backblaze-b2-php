<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Classes;

use function sizeof;
use function rawurlencode;

use RuntimeException;

/**
 * @link https://www.backblaze.com/b2/docs/files.html#fileInfo
 *
 * @package BackblazeB2\Classes
 */
abstract class AbstractObjectInfo
{

	public const HEADER_PREFIX = 'X-Bz-Info-';

	/** @var array */
	private $data;

	public function __construct(array $data = [])
	{
		if (sizeof($data) > 10) {
			throw new RuntimeException('Custom file information can only be up to 10 key/value pairs.');
		}

		$this->data = $data;
	}

	public function empty(): void
	{
		$this->data = [];
	}

	public function unset(string $key): AbstractObjectInfo
	{
		unset($this->data[$key]);

		return $this;
	}

	public function set(string $key, $value): AbstractObjectInfo
	{
		if ($this->size() >= 10) {
			throw new RuntimeException('Custom object information can only be up to 10 key/value pairs.');
		}

		$this->data[$key] = $value;

		return $this;
	}

	/**
	 *
	 * @param null|string $key
	 * @param mixed       $default
	 * @return string|array
	 */
	public function get(?string $key = null, $default = null)
	{
        if ($key === null) {
            if (count($this->data)) {
                return $this->data;
            }
            return null;
        }

		return $this->data[$key] ?? $default;
	}

	public function size(): int
	{
		return sizeof($this->data);
	}

	/**
	 * Get the file info as headers for the B2 API.
	 *
	 * @return array Array of the file info, keys beginning with `X-Bz-Info-`.
	 */
	public function getHeaders(): array
	{
		$headers = [];

		foreach ($this->data as $key => $value) {
			$headers[static::HEADER_PREFIX . $key] = rawurlencode((string) $value);
		}

		return $headers;
	}

	abstract public static function fromArray(array $data): AbstractObjectInfo;

	/** @inheritdoc */
	/*
	public function jsonSerialize(): array
	{
		return [];
	}
	*/
}
