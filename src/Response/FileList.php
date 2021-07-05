<?php

namespace Zaxbux\BackblazeB2\Response;

use Generator;
use Psr\Http\Message\ResponseInterface;
use Zaxbux\BackblazeB2\Object\File;

use function GuzzleHttp\json_decode;

/** @package Zaxbux\BackblazeB2\Response */
class FileList extends AbstractListResponse {
	
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
			$this->files        = static::createObjectIterable(File::class, $files);
			$this->nextFileId   = $nextFileId;
			$this->nextFileName = $nextFileName;
	}

	/**
	 * Get the value of files.
	 * 
	 * @return Generator
	 */ 
	public function getFiles(): Generator
	{
		return $this->files;
	}

	/**
	 * Get the value of files.
	 * 
	 * @return iterable<File>
	 */ 
	public function getFilesArray(): iterable
	{
		return iterator_to_array($this->getFiles());
	}

	/**
	 * 
	 * @return null|File 
	 */
	public function first(): ?File
	{
		return $this->getFiles()->current();
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
	 * @return FileList
	 */
	public static function create(ResponseInterface $response): FileList
	{
		$data = json_decode((string) $response->getBody(), true);

		return new FileList(
			$data[File::ATTRIBUTE_FILES],
			// Not set when listing files by name
			$data[File::ATTRIBUTE_NEXT_FILE_ID] ?? null,
			// Not set when listing large files
			$data[File::ATTRIBUTE_NEXT_FILE_NAME] ?? null
		);
	}
}