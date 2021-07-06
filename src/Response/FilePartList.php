<?php

namespace Zaxbux\BackblazeB2\Response;

use Psr\Http\Message\ResponseInterface;
use Zaxbux\BackblazeB2\Object\File;

use function GuzzleHttp\json_decode;


class FilePartList extends AbstractListResponse {
	
	/** @var iterable<File> */
	private $parts;
	
	/** @var string */
	private $nextPartNumber;

	public function __construct(
		array $parts,
		?string $nextPartNumber = null
	) {
			$this->files      = static::createObjectIterable(File::class, $parts);
			$this->nextPartNumber = $nextPartNumber;
	}

	/**
	 * Get the value of parts.
	 * 
	 * @return iterable<File>
	 */ 
	public function getParts(): iterable
	{
		return $this->parts;
	}

	/**
	 * Get the value of nextPartNumber.
	 */ 
	public function getNextPartNumber(): ?string
	{
		return $this->nextPartNumber;
	}

	/**
	 * @inheritdoc
	 * 
	 * @return FilePartList
	 */
	public static function create(ResponseInterface $response): FilePartList
	{
		$responseData = json_decode((string) $response->getBody(), true);

		return new FilePartList(
			$responseData[File::ATTRIBUTE_PARTS],
			$responseData[File::ATTRIBUTE_NEXT_PART_NUMBER]
		);
	}
}