<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2;

use GuzzleHttp\ClientInterface;
use Zaxbux\BackblazeB2\Config;
use Zaxbux\BackblazeB2\Object\AccountAuthorization;
use Zaxbux\BackblazeB2\Service\ApplicationKeyService;
use Zaxbux\BackblazeB2\Service\BucketService;
use Zaxbux\BackblazeB2\Service\FileService;
use Zaxbux\BackblazeB2\Traits\AuthorizationHeaderTrait;
use Zaxbux\BackblazeB2\Traits\BucketServiceHelpersTrait;
use Zaxbux\BackblazeB2\Traits\DeleteAllFilesTrait;
use Zaxbux\BackblazeB2\Traits\FileServiceHelpersTrait;

/** @package Zaxbux\BackblazeB2 */
class Client
{
	use FileServiceHelpersTrait;
	use BucketServiceHelpersTrait;
	use AuthorizationHeaderTrait;

	use FileService;
	use BucketService;
	use ApplicationKeyService;

	public const B2_API_CLIENT_VERSION  = '2.0.0';
	public const B2_API_BASE_URL        = 'https://api.backblazeb2.com';
	public const B2_API_V2              = '/b2api/v2';

	/** @var ClientInterface */
	public $guzzle;

	/** @var AccountAuthorization */
	protected $accountAuthorization;

	/**
	 * Create a new instance of the B2 API client for PHP.
	 * 
	 * @param array|Config $config One of three possible values:
	 *                               1. An array with application keys: `["application_key_id", "application_key"]`
	 *                               2. An associative array of config options
	 *                               3. An instance of a configuration object (@see \Zaxbux\BackblazeB2\Config)
	 */
	public function __construct($config)
	{
		$this->config = Config::fromArray($config);
	}

	public function guzzle(): ClientInterface
	{
		return $this->config->client();
	}

	/**
	 * Authorize the B2 account in order to get an authorization token and API URLs.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_authorize_account.html
	 */
	public static function authorizeAccount(
		string $applicationKeyId,
		string $applicationKey,
		ClientInterface $client
	): AccountAuthorization {
		$response = $client->request('GET', static::B2_API_BASE_URL . static::B2_API_V2 . '/b2_authorize_account', [
			'headers' => [
				'Authorization' => static::getBasicAuthorization($applicationKeyId, $applicationKey),
			],
		]);

		return AccountAuthorization::fromResponse($response);
	}
}
