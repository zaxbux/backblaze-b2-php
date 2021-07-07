<?php

namespace Zaxbux\BackblazeB2\Response;

use Iterator;
use Psr\Http\Message\ResponseInterface;
use Zaxbux\BackblazeB2\Object\File;
use Zaxbux\BackblazeB2\Utils;

/** @package Zaxbux\BackblazeB2\Response */
class FileList extends AbstractListResponse {
	
	/** @var Iterator<File> */
	private $files;
	
	/** @var string */
	private $nextFileId;
	
	/** @var string */
	private $nextFileName;

	public function __construct(
		iterable $files,
		?string $nextFileId = null,
		?string $nextFileName = null
	) {
			$this->files        = $files;
			$this->nextFileId   = $nextFileId;
			$this->nextFileName = $nextFileName;
	}

	/**
	 * Get the value of files.
	 */ 
	public function getFiles(): Iterator
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
	public static function fromResponse(ResponseInterface $response): FileList
	{
		$data = Utils::jsonDecode((string) $response->getBody(), true);

		return new FileList(
			static::createObjectIterable(File::class, $data[File::ATTRIBUTE_FILES]),
			// Not set when listing files by name
			$data[File::ATTRIBUTE_NEXT_FILE_ID] ?? null,
			// Not set when listing large files
			$data[File::ATTRIBUTE_NEXT_FILE_NAME] ?? null
		);
	}
}