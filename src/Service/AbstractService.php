<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Service;

use function sprintf;

use GuzzleHttp\ClientInterface;
use InvalidArgumentException;
use Zaxbux\BackblazeB2\Object\AccountAuthorization;
use Zaxbux\BackblazeB2\Client;

abstract class AbstractService
{
	/** @var Client */
	protected $client;

	/** @var ClientInterface */
	public $guzzle;

	public function __construct(Client $client, ClientInterface $guzzle)
	{
		$this->client = $client;
		$this->guzzle = $guzzle;
	}
	
	public function getAccountAuthorization(): ?AccountAuthorization
	{
		return $this->client->getAccountAuthorization();
	}
	
	/**
	 * Throw an exception if required keys are missing and remove optional keys if the value is NULL.
	 * Required parameters will take precedence over optional parameters.
	 * 
	 * @param array $required Mandatory parameters for the request.
	 * @param array $optional Optional parameters for the request.
	 *                        Can be omitted if only checking required parameters.
	 * 
	 * @return array Required parameters merged with optional parameters.
	 * 
	 * @throws InvalidArgumentException If a required option is `null`.
	 */
	public static function filterRequestOptions(array $required, ?array $optional = []): array {
		// Ensure required options have a non-NULL value.
		foreach ($required as $name => $value) {
			if ($value === null) {
				throw new InvalidArgumentException(sprintf('The %s parameter is required and cannot be null.', $name));
			}
		}

		// Remove options with NULL values
		return array_filter($optional) + $required;
	}
}