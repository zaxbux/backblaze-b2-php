<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Classes;

use function hash_init, hash_update_stream, hash_final;
use function sha1, fstat, rewind, mb_strlen, is_resource;

/**
 * Calculate the length and hash of a string or stream.
 * 
 * @package Zaxbux\BackblazeB2\Classes
 */
final class FileUploadMetadata {
	
	/** @var int */
	private $length;
	
	/** @var string */
	private $hash;

	/**
	 * @param int $length 
	 * @param string $hash 
	 * @return void 
	 */
	public function __construct(int $length, string $hash)
	{
		$this->length = $length;
		$this->hash = $hash;
	}

	/**
	 * 
	 * @param string|resource $body The resource used to calculate a length in bytes and SHA1 hash.
	 * @return FileUploadMetadata
	 */
	public static function fromResource(mixed $body)
	{
		if (is_resource($body)) {
			// Calculate the file's hash incrementally from the stream.
			$context = hash_init('sha1');
			hash_update_stream($context, $body);
			$hash = hash_final($context);

			// Get the length of the stream.
			$length = fstat($body)['length'];

			// Rewind the stream before passing it to the HTTP client.
			rewind($body);

			return static($length, $hash);
		}

		// Calculate the length and hash of a string.
		$hash = sha1($body);
		$length = mb_strlen($body);

		return static($length, $hash);
	}

	/**
	 * Get the length of the upload body in bytes.
	 */ 
	public function getLength(): int
	{
		return $this->length;
	}

	/**
	 * Get the SHA1 hash of the upload body.
	 */ 
	public function getSha1(): string
	{
		return $this->hash;
	}
}