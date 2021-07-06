<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Response;

use function sprintf;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/** 
 * A response representing a file download.
 * 

 */
class FileDownload extends AbstractResponse {

	/** @var string */
	private $filePath;

	public function __construct(
		ResponseInterface $response,
		?string $filePath = null
	) {
		parent::__construct($response);
		$this->filePath = $filePath;
	}

	/**
	 * Get the contents of the file stream or read the file from disk.
	 * 
	 * @return string The contents of the file.
	 * 
	 * @throws RuntimeException If the stream is not readable or the file does not exist.
	 */
	public function getContents(): string {
		if ($this->isStream() && $this->getStream()->isReadable()) {
			return $this->getStream()->getContents();
		}

		if ($this->isFile() && is_file($this->filePath)) {
			return file_get_contents($this->filePath);
		}

		throw new RuntimeException(sprintf('The file %s cannot be read.', $this->filePath ?: '[Stream]'));
	}

	/**
	 * Get the response body.
	 * 
	 * @return null|StreamInterface
	 */ 
	public function getStream(): StreamInterface
	{
		return $this->isStream() ? $this->rawResponse->getBody() : null;
	}

	/**
	 * Get the file path on disk.
	 * 
	 * @return string 
	 */
	public function getFilePath(): string
	{
		return $this->filePath;
	}

	/**
	 * Get the response headers.
	 * 
	 * @return string[][]
	 */ 
	public function getHeaders(): array
	{
		return $this->rawResponse->getHeaders();
	}

	/**
	 * @return bool Check if the response is a stream.
	 */
	public function isStream(): bool
	{
		return $this->filePath === null;
	}

	/**
	 * @return bool Check if the response was saved to disk.
	 */
	public function isFile(): bool
	{
		return !$this->isStream();
	}

	/**
	 * @inheritdoc
	 * 
	 * @param null|string $filePath 
	 * 
	 * @return FileDownload
	 */
	public static function create(
		ResponseInterface $response,
		?string $filePath = null
	): FileDownload {
		return new FileDownload($response, $filePath);
	}
}