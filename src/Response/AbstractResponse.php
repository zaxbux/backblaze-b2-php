<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Response;

use Psr\Http\Message\ResponseInterface;


abstract class AbstractResponse {

	/** @var ResponseInterface */
	protected $rawResponse;

	/**
	 * Create a new instance of this class and populate it.
	 * 
	 * @param ResponseInterface $response B2 API response.
	 * @return AbstractListResponse 
	 */
	abstract public static function create(ResponseInterface $response): AbstractResponse;

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
}
