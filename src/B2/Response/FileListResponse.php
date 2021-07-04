<?php

namespace Zaxbux\BackblazeB2\B2\Response;

use Psr\Http\Message\ResponseInterface;
use Zaxbux\BackblazeB2\B2\Object\File;
use Zaxbux\BackblazeB2\Class\ListResponseBase;

use function GuzzleHttp\json_decode;

/** @package Zaxbux\BackblazeB2\B2\Response */
class FileListResponse extends ListResponseBase {
	
	/** @var iterable<File> */
	private $files;
	
	/** @var string */
	private $nextFileId;
	
	/** @var string */
	private $nextFileName;

	public function __construct(
		array $files,
		?string $nextFileId = null,
		?string $nextFileName = null
	) {
			$this->files        = $this->createObjectIterable(File::class, $files);
			$this->nextFileId   = $nextFileId;
			$this->nextFileName = $nextFileName;
	}

	/**
	 * Get the value of files.
	 * 
	 * @return iterable<File>
	 */ 
	public function getFiles(): iterable
	{
		return $this->files;
	}

	/**
	 * Get the value of nextFileId.
	 */ 
	public function getNextFileId(): ?string
	{
		return $this->nextFileId;
	}

	/**
	 * Get the value of nextFileName.
	 */ 
	public function getNextFileName(): ?string
	{
		return $this->nextFileName;
	}

	/**
	 * @inheritdoc
	 * 
	 * @return FileListResponse
	 */
	public static function create(ResponseInterface $response): FileListResponse
	{
		$responseData = json_decode((string) $response->getBody());

		return static($responseData->files, $responseData->nextFileId, $responseData->nextFileName);
	}
}