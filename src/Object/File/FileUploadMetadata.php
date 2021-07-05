<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Object\File;

use function hash_init, hash_update_stream, hash_final;
use function sha1, fstat, rewind, strlen, is_resource;

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
	public function __construct(int $length, string $hash, ?int $mtime = null)
	{
		$this->length = $length;
		$this->hash = $hash;
		$this->mtime = $mtime > 0 ? $mtime : null;
	}

	/**
	 * 
	 * @param string|resource $body The resource used to calculate a length in bytes and SHA1 hash.
	 * @return FileUploadMetadata
	 */
	public static function fromResource($body)
	{
		if (is_resource($body)) {
			// Calculate the file's hash incrementally from the stream.
			$context = hash_init('sha1');
			hash_update_stream($context, $body);
			$hash = hash_final($context);

			// Get the length and mtime of the stream.
			$fileInfo = fstat($body);

			// Rewind the stream before passing it to the HTTP client.
			rewind($body);

			return new FileUploadMetadata($fileInfo['size'], $hash, $fileInfo['mtime'] ?? null);
		}

		// Calculate the length and hash of a string.
		$hash = sha1($body);
		$length = strlen($body);

		return new FileUploadMetadata($length, $hash);
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

	/**
	 * Get the last modified timestamp in seconds since the UNIX epoch.
	 */ 
	public function getLastModifiedTimestamp(): ?int
	{
		return $this->mtime;
	}

	/**
	 * 
	 * @param resource $stream 
	 * @return int 
	 */
	public static function resourceLength($stream): int
	{
		return fstat($stream)['size'];
	}
}