<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Traits;

use Psr\Http\Message\ResponseInterface;
use Zaxbux\BackblazeB2\Utils;

/** @package BackblazeB2\Traits */
trait ResponseTrait {

	/** @var ResponseInterface */
	protected $rawResponse;

	/**
	 * Create a new instance of this class and populate it.
	 * 
	 * @param ResponseInterface $response B2 API response.
	 */
	abstract public static function fromResponse(ResponseInterface $response);

	/*public function __construct(ResponseInterface $response)
	{
		$this->rawResponse = $response;
	}*/

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
		return Utils::jsonDecode($this->rawResponse);
	}
}
