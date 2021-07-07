<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Response;

use JsonException;
use Psr\Http\Message\ResponseInterface;
use Zaxbux\BackblazeB2\Utils;

/** @package Zaxbux\BackblazeB2\Response */
abstract class AbstractResponse {

	/** @var ResponseInterface */
	protected $rawResponse;

	/**
	 * Create a new instance of this class and populate it.
	 * 
	 * @param ResponseInterface $response B2 API response.
	 * @return AbstractListResponse 
	 */
	abstract public static function fromResponse(ResponseInterface $response): AbstractResponse;

	public function __construct(ResponseInterface $response)
	{
		$this->rawResponse = $response;
	}

	/**
	 * Get the raw response.
	 * 
	 * @return ResponseInterface
	 */
	public function getRawResponse(): ResponseInterface
	{
		return $this->rawResponse;
	}

	/**
	 * Decode the response body as JSON and return the array.
	 * 
	 * @return null|array Returns null if the response could not be decoded as JSON.
	 */
	public function json(): ?array
	{
		try {
			return Utils::jsonDecode((string) $this->rawResponse->getBody());
		} catch (JsonException $ex) {}

		return null;
	}
}
