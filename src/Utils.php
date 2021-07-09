<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2;

use InvalidArgumentException;
use GuzzleHttp\Utils as GuzzleUtils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/** @package BackblazeB2 */
final class Utils
{
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
	public static function filterRequestOptions(array $required, ...$optional): array {
		// Ensure required options have a non-NULL value.
		foreach ($required as $name => $value) {
			if ($value === null) {
				throw new InvalidArgumentException(sprintf('The %s parameter is required and cannot be null.', $name));
			}
		}

		$options = [];

		foreach ($optional as $set) {
			if (!isset($set)) continue;
			
			if (is_array($set)) {
				// Remove options with NULL values
				$set += array_filter($set);
			}

			$options += $set;
		}

		return $options + $required;
	}
	
	/**
	 * Encodes the account application key ID and application key as a Basic Authorization header value.
	 * 
	 * @param string $applicationKeyId The application key ID.
	 * @param string $applicationKey   The application key.
	 * 
	 * @return string An HTTP Basic Authorization header value.
	 */
	public static function basicAuthorization(string $applicationKeyId, string $applicationKey): string {
		return 'Basic ' . base64_encode($applicationKeyId . ':' . $applicationKey);
	}

	public static function joinFilePaths(...$paths): string
	{
		return static::joinPathsGeneric($paths, DIRECTORY_SEPARATOR);
	}

	public static function joinPaths(...$paths): string
	{
		return static::joinPathsGeneric($paths, '/');
	}

	/**
	 * @param string[] $paths 
	 * @param string   $separator
	 * @return string 
	 */
	private static function joinPathsGeneric(array $paths, string $separator)
	{
		if (is_array($paths[0])) {
			$paths = $paths[0];
		}

		$paths = array_filter(array_map(function ($path) use ($separator) {
			return rtrim($path, $separator);
		}, $paths));

		return join($separator, $paths);
	}

	public static function isStream($var): bool
	{
		return !is_string($var) && (is_resource($var) || $var instanceof StreamInterface);
	}

	public static function getUserAgent(string $appName): string
	{
		return sprintf('%s %s+php/%s %s', $appName, Client::USER_AGENT_PREFIX . Client::VERSION, PHP_VERSION, GuzzleUtils::defaultUserAgent());
	}

	/**
	 * 
	 * @param string|ResponseInterface $data 
	 * @return array 
	 */
	public static function jsonDecode($data): array
	{
		if ($data instanceof ResponseInterface) {
			$data = (string) $data->getBody();
		}

		return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
	}
}