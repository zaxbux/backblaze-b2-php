<?php

namespace Zaxbux\BackblazeB2\B2\Response;

use Psr\Http\Message\ResponseInterface;
use Zaxbux\BackblazeB2\Class\ListResponseBase;

use function GuzzleHttp\json_decode;

/** @package Zaxbux\BackblazeB2\B2\Response */
class FilePartListResponse extends ListResponseBase {
	
	/** @var iterable<File> */
	private $parts;
	
	/** @var string */
	private $nextPartNumber;

	public function __construct(
		array $parts,
		?string $nextPartNumber = null
	) {
			$this->files      = $this->createObjectIterable(File::class, $parts);
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
	 * @return FilePartListResponse
	 */
	public static function create(ResponseInterface $response): FilePartListResponse
	{
		$responseData = json_decode((string) $response->getBody());

		return static($responseData->parts, $responseData->nextPartNumber);
	}
}