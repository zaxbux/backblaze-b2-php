<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2;

use Exception;
use InvalidArgumentException;

use GuzzleHttp\ClientInterface;

use Zaxbux\BackblazeB2\B2ObjectBase\Bucket;
use Zaxbux\BackblazeB2\B2ObjectBase\BucketType;
use Zaxbux\BackblazeB2\B2ObjectBase\DownloadAuthorization;
use Zaxbux\BackblazeB2\B2ObjectBase\File;
use Zaxbux\BackblazeB2\B2ObjectBase\Key;

use Zaxbux\BackblazeB2\Client\AccountAuthorization;
use Zaxbux\BackblazeB2\Client\IAuthorizationCache;

use Zaxbux\BackblazeB2\Http\ClientFactory;

use Zaxbux\BackblazeB2\Client\Exception\NotFoundException;
use Zaxbux\BackblazeB2\Client\Exception\ValidationException;
use Zaxbux\BackblazeB2\Client\Exception\UnauthorizedException;
use Zaxbux\BackblazeB2\Http\Config;

class Client
{
	public const CLIENT_VERSION  = '2.0.0';
	public const B2_API_BASE_URL = 'https://api.backblazeb2.com';
	public const B2_API_V2       = '/b2api/v2';

	/** @var string */
	protected $applicationKeyId;

	/** @var string */
	protected $applicationKey;

	/** @var AccountAuthorization */
	protected $accountAuthorization;

	/** @var IAuthorizationCache */
	protected $authorizationCache;

	/** @var GuzzleClient */
	protected $client;

	/**
	 * Client constructor.
	 *
	 * @param string $applicationKeyId The identifier for the key. The account ID can also be used.
	 * @param string $applicationKey   The secret part of the key. The master application key can also be used.
	 * 
	 * @param IAuthorizationCache|null $authorizationCache [optional] An object implementing an authorization cache.
	 * @param ClientInterface|null     $client             [optional] A client compatible with GuzzleHttp.
	 */
	public function __construct(
		string $applicationKeyId,
		string $applicationKey,
		?IAuthorizationCache $authorizationCache = null,
		?ClientInterface $client = null
	) {
		$this->applicationKeyId   = $applicationKeyId;
		$this->applicationKey     = $applicationKey;
		$this->authorizationCache = $authorizationCache;
		//$this->accountAuthorization = AccountAuthorization::fromArray([]);

		$config = new Config();
		$config->client = $this;

		$this->client = $client ?: ClientFactory::create($config);
	}

	public function getApplicationKeyId(): string
	{
		return $this->getApplicationKeyId;
	}

	public function getApplicationKey(): string
	{
		return $this->getApplicationKey;
	}

	public function getAccountAuthorization(): ?AccountAuthorization
	{
		return $this->accountAuthorization;
	}

	public function setAccountAuthorization(AccountAuthorization $accountAuthorization): void
	{
		$this->accountAuthorization = $accountAuthorization;
	}

	/**
	 * Authorize the B2 account in order to get an auth token and API/download URLs.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_authorize_account.html
	 * 
	 * @throws \Exception
	 */
	protected function authorize()
	{
		// Try to fetch existing authorization token from cache.
		if ($this->authorizationCache instanceof IAuthorizationCache) {
			$this->accountAuthorization = $this->authorizationCache->get($this->applicationKeyId);
		}

		// Fetch a new authorization token from the API.
		if (!$this->accountAuthorization) {
			$this->accountAuthorization = AccountAuthorization::refresh($this->applicationKeyId, $this->applicationKey);

			// Cache the new authorization token.
			if ($this->authorizationCache instanceof IAuthorizationCache && !$this->accountAuthorization) {
				$this->authorizationCache->put($this->applicationKeyId, $this->accountAuthorization);
			}
		}

		if (empty($this->accountAuthorization)) {
			throw new \Exception('Failed to authorize account.');
		}
	}

	/**
	 * Cancel the upload of a large file, and deletes all of the parts that have been uploaded.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_cancel_large_file.html
	 * 
	 * @param string $fileId The ID returned by `b2_start_large_file`.
	 * 
	 * @return array
	 */
	public function cancelLargeFile(string $fileId)
	{
		$response = $this->client->request('POST', '/b2_cancel_large_file', [
			'json' => [
				'fileId' => $fileId,
			],
		]);

		return $response;
	}

	/**
	 * Creates a new file by copying from an existing file.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_copy_file.html
	 * 
	 * @param string $sourceFileId        The ID of the source file being copied.
	 * @param string $fileName            The name of the new file being created.
	 * @param string $destinationBucketId The ID of the bucket where the copied file will be stored. If this is not
	 *                                    set, the copied file will be added to the same bucket as the source file.
	 *                                    Note that the bucket containing the source file and the destination bucket
	 *                                    must belong to the same account. 
	 * @param string $range               The range of bytes to copy. If not provided, the whole source file will be 
	 *                                    copied.
	 * @param string $metadataDirective   The strategy for how to populate metadata for the new file.
	 * @param string $contentType         Must only be supplied if the metadataDirective is REPLACE. Use the
	 *                                    Content-Type b2/x-auto to automatically set the stored Content-Type post
	 *                                    upload.
	 * @param array $fileInfo             Must only be supplied if the metadataDirective is REPLACE. This field stores
	 *                                    the metadata that will be stored with the file.
	 * 
	 * @return File
	 * 
	 * @throws InvalidArgumentException
	 */
	public function copyFile(
		string $sourceFileId,
		string $fileName,
		string $destinationBucketId = null,
		string $range = null,
		string $metadataDirective = null,
		string $contentType = null,
		array $fileInfo = null
	) {
		$json = [
			'sourceFileId' => $sourceFileId,
			'fileName'     => $fileName,
		];

		if ($range) {
			$json['range'] = $range;
		}

		if ($metadataDirective) {
			if ($metadataDirective == File::METADATA_DIRECTIVE_REPLACE && $contentType == null) {
				$contentType = File::CONTENT_TYPE_AUTO;
			}

			if ($metadataDirective == File::METADATA_DIRECTIVE_COPY && $contentType) {
				throw new InvalidArgumentException(File::ATTRIBUTE_CONTENT_TYPE . ' must not be set when metadataDirective is ' . File::METADATA_DIRECTIVE_COPY);
			}

			if ($metadataDirective == File::METADATA_DIRECTIVE_COPY && $fileInfo) {
				throw new InvalidArgumentException(File::ATTRIBUTE_FILE_INFO . ' must not be set when metadataDirective is ' . File::METADATA_DIRECTIVE_COPY);
			}

			$json['metadataDirective'] = $metadataDirective;
		}

		if ($contentType) {
			$json['contentType'] = $contentType;
		}

		if ($fileInfo) {
			$json['fileInfo'] = $fileInfo;
		}

		$response = $this->client->request('POST', '/b2_copy_file', [
			'json' => $json
		]);

		return File::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Copies from an existing B2 file, storing it as a part of a large file which has already been started with
	 * `b2_start_large_file`.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_copy_part.html
	 * 
	 * @param string $sourceFileId The ID of the source file being copied.
	 * @param string $largeFileId  The ID of the large file the part will belong to.
	 * @param int    $partNumber   A number from 1 to 10000. The parts uploaded for one file must have contiguous
	 *                             numbers, starting with 1.
	 * @param string $range        The range of bytes to copy. If not provided, the whole source file will be copied.
	 * 
	 * @return File
	 */
	public function copyPart(string $sourceFileId, string $largeFileId, int $partNumber, string $range = null): File
	{
		$json = [
			'sourceFileId' => $sourceFileId,
			'largeFileId'  => $largeFileId,
			'partNumber'   => $partNumber,
		];

		if ($range) {
			$json['range'] = $range;
		}

		$response = $this->client->request('POST', '/b2_copy_part', [
			'json' => $json
		]);

		return File::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Create a bucket with the given name and type.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_create_bucket.html
	 *
	 * @param string           $bucketName     The name to give the new bucket.
	 * @param string           $bucketType     Either "allPublic", meaning that files in this bucket can be downloaded
	 *                                         by anybody, or "allPrivate", meaning that you need a bucket 
	 *                                         authorization token to download the files. 
	 * @param array            $bucketInfo     User-defined information to be stored with the bucket.
	 * @param CORSRule[]       $corsRules      The initial CORS rules for this bucket.
	 * @param LifecycleRule[]  $lifecycleRules The initial lifecycle rules for this bucket.
	 * 
	 * @return Bucket
	 * 
	 * @throws InvalidArgumentException
	 */
	public function createBucket(
		string $bucketName,
		string $bucketType = BucketType::PRIVATE,
		array $bucketInfo = null,
		array $corsRules = null,
		array $lifecycleRules = null
	) {
		if (!in_array($bucketType, [BucketType::PUBLIC, BucketType::PRIVATE])) {
			throw new InvalidArgumentException(sprintf(
				'$bucketType must be %s or %s',
				BucketType::PRIVATE,
				BucketType::PUBLIC
			));
		}

		$json = [
			'accountId'  => $this->accountAuthorization->getAccountId(),
			'bucketName' => $bucketName,
			'bucketType' => $bucketType,
		];

		if ($bucketInfo) {
			$json['bucketInfo'] = $bucketInfo;
		}

		if ($corsRules) {
			$json['corsRules'] = $corsRules;
		}

		if ($lifecycleRules) {
			$json['lifecycleRules'] = $lifecycleRules;
		}

		$response = $this->client->request('POST', '/b2_create_bucket', [
			'json' => $json
		]);

		return Bucket::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Creates a new application key.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_create_key.html
	 * 
	 * @param string    $keyName       A name for this key. There is no requirement that the name be unique. The name cannot be used to look up the key.
	 * @param string[]  $capabilities  A list of strings, each one naming a capability the new key should have.
	 * @param int       $validDuration The number of seconds before the key expires.
	 * @param string    $bucketId      Restrict access a specific bucket.
	 * @param string    $namePrefix    Restrict access to files whose names start with the prefix. $bucketId must also
	 *                                 be provided.
	 * 
	 * @return array
	 * 
	 * @throws UnauthorizedException
	 * @throws ValidationException
	 * @throws InvalidArgumentException
	 */
	public function createKey(
		string $keyName,
		array $capabilities,
		int $validDuration = null,
		string $bucketId = null,
		string $namePrefix = null
	) {

		if (empty($capabilities)) {
			throw new InvalidArgumentException('capabilities must contain least one valid item');
		}

		$json = [
			'accountId'    => $this->accountAuthorization->getAccountId(),
			'capabilities' => $capabilities,
			'keyName'      => $keyName,
		];

		if ($validDuration) {
			$json['validDurationInSeconds'] = $validDuration;
		}

		if ($bucketId) {
			if (count(array_diff($capabilities, [
				'listBuckets',
				'listFiles',
				'readFiles',
				'shareFiles',
				'writeFiles',
				'deleteFiles'
			])) > 0) {
				throw new ValidationException('Invalid capabilities when bucketId provided');
			}
			$json['bucketId'] = $bucketId;
		}

		if ($namePrefix) {
			if (!$bucketId) {
				throw new ValidationException('bucketId required with namePrefix');
			}
			$json['namePrefix'] = $namePrefix;
		}

		$response = $this->client->request('POST', '/b2_create_key', [
			'json' => $json,
		]);

		return Key::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Deletes the bucket specified. Only buckets that contain no version of any files can be deleted.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_delete_bucket.html 
	 *
	 * @param string $bucketId The ID of the bucket to delete.
	 * 
	 * @return Bucket
	 */
	public function deleteBucket(string $bucketId)
	{
		$response = $this->client->request('POST', '/b2_delete_bucket', [
			'json' => [
				'accountId' => $this->accountAuthorization->getAccountId(),
				'bucketId'  => $bucketId
			]
		]);

		return Bucket::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Deletes one version of a file.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_delete_file_version.html
	 *
	 * @param string $fileName         The name of the file.
	 * @param string $fileId           The ID of the file.
	 * @param bool   $bypassGovernance Must be specified and set to true if deleting a file version protected by
	 *                                 File Lock governance mode retention settings.
	 * 
	 * @return array
	 */
	public function deleteFileVersion(string $fileName, string $fileId, ?bool $bypassGovernance = false): File
	{
		$response = $this->client->request('POST', '/b2_delete_file_version', [
			'json' => [
				'fileName' => $fileName,
				'fileId'   => $fileId
			]
		]);

		return File::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Deletes all versions of a file in a bucket.
	 * 
	 * @see Client::deleteFileVersion()
	 * 
	 * @param string $bucketId 
	 * @param string $fileName 
	 * @param null|bool $bypassGovernance 
	 */
	public function deleteAllFileVersions(string $bucketId, string $fileName, ?bool $bypassGovernance = false): void
	{
		$fileVersions = $this->listFileVersions($bucketId, $fileName);

		foreach ($fileVersions as $version) {
			$this->deleteFileVersion($fileName, $version['id']);
		}
	}

	/**
	 * Deletes the application key specified.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_delete_key.html
	 *
	 * @param string $applicationKeyId The key to delete.
	 * 
	 * @return array
	 */
	public function deleteKey(string $applicationKeyId)
	{
		$response = $this->client->request('POST', '/b2_delete_key', [
			'json' => [
				'applicationKeyId' => $applicationKeyId,
			]
		]);

		return Key::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Downloads one file from B2 by File ID.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_download_file_by_id.html
	 * 
	 * @param string $fileId      The file ID to download.
	 * @param array  $options     An optional array of additional B2 API options. Setting $options['stream'] to true to
	 *                            stream a response. {@link http://docs.guzzlephp.org/en/stable/request-options.html#stream}
	 * @param string $range       A standard RFC 7233 byte-range request, which will return just part of the stored file.
	 * @param mixed  $sink        A string, stream, or StreamInterface that specifies where to save the file.
	 *                            {@link http://docs.guzzlephp.org/en/stable/request-options.html#sink}
	 * @param bool   $headersOnly Only get the file headers, without downloading the whole file.
	 * 
	 * @return array
	 */
	public function downloadFileById(
		string $fileId,
		array $options = null,
		string $range = null,
		$sink = null,
		bool $headersOnly = false
	) {
		$downloadUrl = sprintf('%s/b2_download_file_by_id', $this->accountAuthorization->getDownloadUrl() . static::B2_API_V2);

		$query = ['fileId' => $fileId];

		return $this->download($downloadUrl, $query, $options, $range, $sink, $headersOnly);
	}

	/**
	 * Downloads one file from B2 by File Name.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_download_file_by_name.html
	 * 
	 * @param string $fileName    The file name to download.
	 * @param string $bucketName  The bucket the file is contained in.
	 * @param array  $options     An optional array of additional B2 API options. Setting $options['stream'] to true to
	 *                            stream a response. {@link http://docs.guzzlephp.org/en/stable/request-options.html#stream}
	 * @param string $range       A standard RFC 7233 byte-range request, which will return just part of the stored file.
	 * @param mixed  $sink        A string, stream, or StreamInterface that specifies where to save the file.
	 *                            {@link http://docs.guzzlephp.org/en/stable/request-options.html#sink}
	 * @param bool   $headersOnly Only get the file headers, without downloading the whole file.
	 * 
	 * @return array
	 */
	public function downloadFileByName(
		string $fileName,
		string $bucketName,
		array $options = null,
		string $range = null,
		$sink = null,
		bool $headersOnly = false
	) {
		$downloadUrl = sprintf('%s/file/%s/%s', $this->accountAuthorization->getApiUrl(), $bucketName, $fileName);

		return $this->download($downloadUrl, [], $options, $range, $sink, $headersOnly);
	}

	/**
	 * Internal method to download and stream files.
	 * 
	 * @param string $downloadUrl The URL to make the request to.
	 * @param array  $query       Query paramaters.
	 * @param array  $options     Additional options for the B2 API.
	 * @param string $range       A standard RFC 7233 byte-range request, which will return just part of the stored
	 *                            file.
	 * @param mixed  $sink        A string, stream, or StreamInterface that specifies where to save the file.
	 * @param bool   $headersOnly Only get the file headers, without downloading the whole file.
	 * 
	 * @return array
	 */
	protected function download($downloadUrl, $query, $options, $range, $sink, $headersOnly)
	{
		$headers = [];

		if (isset($options['b2ContentDisposition'])) {
			$query['b2ContentDisposition'] = $options['b2ContentDisposition'];
		}

		if (isset($options['b2ContentLanguage'])) {
			$query['b2ContentLanguage'] = $options['b2ContentLanguage'];
		}

		if (isset($options['b2Expires'])) {
			$query['b2Expires'] = $options['b2Expires'];
		}

		if (isset($options['b2CacheControl'])) {
			$query['b2CacheControl'] = $options['b2CacheControl'];
		}

		if (isset($options['b2ContentEncoding'])) {
			$query['b2ContentEncoding'] = $options['b2ContentEncoding'];
		}

		if (isset($options['b2ContentType'])) {
			$query['b2ContentType'] = $options['b2ContentType'];
		}

		$stream = isset($options['stream']) && $options['stream'] == true;

		$response = $this->client->request('GET', $downloadUrl, [
			'query'   => $query,
			'headers' => $headers,
			'sink'    => isset($sink) ? $sink : null,
			'stream'  => $stream,
		], false);

		return [
			'headers' => $response->getHeaders(),
			'stream'  => $headersOnly || \is_string($sink) ? null : $response->getBody(),
		];
	}

	/**
	 * Converts the parts that have been uploaded into a single B2 file.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_finish_large_file.html
	 * 
	 * @param string $fileId The ID of the large file.
	 * @param array  An array of SHA1 checksums of the parts of the large file.
	 * 
	 * @return File
	 */
	public function finishLargeFile(string $fileId, array $hashes)
	{
		$response = $this->client->request('POST', '/b2_finish_large_file', [
			'json' => [
				'fileId'        => $fileId,
				'partSha1Array' => $hashes,
			]
		]);

		return File::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Generates an authorization token that can be used to download files
	 * with the specified prefix from a private B2 bucket.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_get_download_authorization.html
	 * 
	 * @param string $bucketId       The identifier for the bucket.
	 * @param string $fileNamePrefix The file name prefix of files the download authorization token will allow access.
	 * @param int    $validDuration  The number of seconds before the authorization token will expire. The minimum
	 *                               value is 1 second. The maximum value is 604800.
	 * @param array  $options        Additional options to pass to the API.
	 */
	public function getDownloadAuthorization(
		string $bucketId,
		string $fileNamePrefix,
		int $validDuration,
		?array $options = null
	): DownloadAuthorization {
		$json = [
			'bucketId'               => $bucketId,
			'fileNamePrefix'         => $fileNamePrefix,
			'validDurationInSeconds' => $validDuration,
		];

		if (isset($options['b2ContentDisposition'])) {
			$json['b2ContentDisposition'] = $options['b2ContentDisposition'];
		}

		if (isset($options['b2ContentLanguage'])) {
			$json['b2ContentLanguage'] = $options['b2ContentLanguage'];
		}

		if (isset($options['b2Expires'])) {
			$json['b2Expires'] = $options['b2Expires'];
		}

		if (isset($options['b2CacheControl'])) {
			$json['b2CacheControl'] = $options['b2CacheControl'];
		}

		if (isset($options['b2ContentEncoding'])) {
			$json['b2ContentEncoding'] = $options['b2ContentEncoding'];
		}

		if (isset($options['b2ContentType'])) {
			$json['b2ContentType'] = $options['b2ContentType'];
		}

		$response = $this->client->request('POST', '/b2_get_download_authorization', [
			'json' => $json
		]);

		return DownloadAuthorization::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Gets information about one file stored in B2.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_get_file_info.html
	 *
	 * @param string $fileId The ID of the file.
	 */
	public function getFileInfo(string $fileId): File
	{
		$response = $this->client->request('POST', '/b2_get_file_info', [
			'json' => [
				'fileId' => $fileId
			]
		]);

		return File::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Gets an URL to use for uploading parts of a large file. 
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_get_upload_part_url.html
	 * 
	 * @param string $fileId The ID of the large file whose parts you want to upload.
	 * 
	 * @return array [ $fileId => string, $uploadUrl => string, $authorizationToken => string ]
	 */
	public function getUploadPartUrl(string $fileId): array
	{
		$response = $this->client->request('POST', '/b2_get_upload_part_url', [
			'json' => [
				'fileId' => $fileId
			]
		]);

		return json_decode((string) $response->getBody(), true);
	}

	/**
	 * Gets an URL to use for uploading files.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_get_upload_url.html
	 * 
	 * @param string $bucketId The ID of the bucket that you want to upload to.
	 * 
	 * @return array [ $bucketId => string, $uploadUrl => string, $authorizationToken => string ]
	 */
	public function getUploadUrl(string $bucketId)
	{
		$response = $this->client->request('POST', '/b2_get_upload_url', [
			'json' => [
				'bucketId' => $bucketId
			]
		]);

		return json_decode((string) $response->getBody(), true);
	}

	/**
	 * Hides a file so that downloading by name will not find the file,
	 * but previous versions of the file are still stored.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_hide_file.html
	 * 
	 * @param string $bucketId
	 * @param string $fileName
	 * 
	 * @return File
	 */
	public function hideFile(string $bucketId, string $fileName)
	{

		$response = $this->client->request('POST', '/b2_hide_file', [
			'json' => [
				'bucketId' => $bucketId,
				'fileName' => $fileName,
			]
		]);

		return File::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Returns a list of bucket objects representing the buckets on the account.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_list_buckets.html
	 * 
	 * @param string[] $bucketTypes Filter buckets by type. Either "allPublic" or "allPrivate" or "snapshot" or "all".
	 *                              If "all" is specified, it must be the only type.
	 * 
	 * @return iterable<Bucket>
	 */
	public function listBuckets(array $bucketTypes = null): iterable
	{
		return $this->_listBuckets(null, false, $bucketTypes);
	}

	public function getBucketById(string $bucketId, array $bucketTypes = null)
	{
		$buckets = $this->_listBuckets($bucketId, false, $bucketTypes);

		if (iterator_count($buckets) !== 1) {
			throw new NotFoundException('Bucket not found.');
		}

		return $buckets[0];
	}

	public function getBucketByName(string $bucketName, array $bucketTypes = null)
	{
		$buckets = $this->_listBuckets($bucketName, true, $bucketTypes);

		if (iterator_count($buckets) !== 1) {
			throw new NotFoundException('Bucket not found.');
		}

		return $buckets[0];
	}

	/**
	 * Returns a list of bucket objects representing the buckets on the account.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_list_buckets.html
	 *
	 * @param string   $bucketId    When bucketId is specified, the result will be a list containing just this bucket,
	 *                              if it's present in the account, or no buckets if the account does not have a
	 *                              bucket with this ID.
	 * @param bool     $listByName  If true, use the value of $bucketId as bucketName
	 * @param string[] $bucketTypes Filter buckets by type. Either "allPublic" or "allPrivate" or "snapshot" or "all".
	 *                              If "all" is specified, it must be the only type.
	 * 
	 * @return iterable<Bucket>
	 */
	private function _listBuckets(string $bucketId = null, bool $listByName = false, array $bucketTypes = null): iterable
	{
		$json = [
			'accountId' => $this->accountAuthorization->getAccountId(),
		];

		$json[$listByName ? 'bucketName' : 'bucketId'] = $bucketId;

		if ($bucketTypes) {
			$json['bucketTypes'] = implode(',', $bucketTypes);
		}

		$response = $this->client->request('POST', '/b2_list_buckets', [
			'json' => $json
		]);

		/*foreach ($response['buckets'] as $bucket) {
			$buckets[] = Bucket::fromArray($bucket);
		}*/

		return Bucket::iterableFromArray(json_decode((string) $response->getBody(), true)->buckets);
	}

	/**
	 * Lists the names of all files in a bucket, starting at a given name.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_list_file_names.html
	 *
	 * @param string $bucketId      The bucket to look for file names in.
	 * @param string $prefix        Files returned will be limited to those with the given prefix. Defaults to the
	 *                              empty string, which matches all files. If not, the first file name after this the
	 *                              first one after this name. 
	 * @param string $delimiter     Files returned will be limited to those within the top folder, or any one
	 *                              subfolder. Folder names will also be returned.
	 *                              The delimiter character will be used to "break" file names into folders.
	 * @param string $startFileName The first file name to return. If there is a file with this name, it will be
	 *                              returned in the list.
	 * @param int    $maxFileCount  The maximum number of files to return from this call. The default value is 100, and
	 *                              the maximum is 10000.
	 *                              The maximum number of files returned per transaction is 1000. If more than 1000 are
	 *                              returned, the call will be billed as multiple transactions.
	 * @param bool   $loop          Make API requests until there are no files left.
	 * 
	 * @return array
	 */
	public function listFileNames(
		string $bucketId,
		string $prefix = '',
		string $delimiter = null,
		string $startFileName = null,
		int $maxFileCount = 1000,
		bool $loop = true
	) {
		$files = [];

		while (true) {
			$response = $this->_listFileNames($bucketId, $prefix, $delimiter, $startFileName, $maxFileCount);

			if (!$loop) {
				return $response;
			}

			array_merge($files, $response['files']);
			$startFileName = $response['nextFileName'];

			if ($response['nextFileName'] == null) {
				break;
			}
		}

		return [
			'files'        => $files,
			'nextFileName' => null,
		];
	}

	/**
	 * Internal method to call the b2_list_file_names API
	 * 
	 * @see Client::listFileNames()
	 * 
	 * @return iterable<File>
	 */
	private function _listFileNames($bucketId, $prefix, $delimiter, $startFileName, $maxFileCount): iterable
	{
		$json = [
			'bucketId'      => $bucketId,
			'maxFileCount'  => $maxFileCount,
		];

		if ($prefix) {
			$json['prefix'] = $prefix;
		}

		if ($delimiter) {
			$json['delimiter'] = $delimiter;
		}

		if ($startFileName) {
			$json['startFileName'] = $startFileName;
		}

		$response = $this->client->request('POST', '/b2_list_file_names', [
			'json' => $json
		]);

		/*
		foreach ($response['files'] as $file) {
			$files[] = new File($file, true);
		}

		$response['files'] = $files;

		return $response;
		*/

		return File::iterableFromArray(json_decode((string) $response->getBody())->files);
	}

	/**
	 * Lists all of the versions of all of the files contained in one bucket, in alphabetical order by file name, and
	 * by reverse of date/time uploaded for versions of files with the same name. 
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_list_file_versions.html
	 * 
	 * @param string $bucketId      The bucket to look for file names in.
	 * @param string $prefix        Files returned will be limited to those with the given prefix. Defaults to the
	 *                              empty string, which matches all files. If not, the first file name after this the
	 *                              first one after this name. 
	 * @param string $delimiter     Files returned will be limited to those within the top folder, or any one
	 *                              subfolder. Folder names will also be returned.
	 *                              The delimiter character will be used to "break" file names into folders.
	 * @param string $startFileName The first file name to return. If there is a file with this name, it will be
	 *                              returned in the list.
	 * @param string $startFileId   
	 * @param int    $maxFileCount  The maximum number of files to return from this call. The default value is 1000, and
	 *                              the maximum is 10000. The maximum number of files returned per transaction is 1000.
	 *                              If more than 1000 are returned, the call will be billed as multiple transactions.
	 * @param bool   $loop          Make API requests until there are no files left.
	 * 
	 * @return array
	 * 
	 * @throws ValidationException
	 */
	public function listFileVersions(
		string $bucketId,
		string $prefix = '',
		string $delimiter = null,
		string $startFileName = null,
		string $startFileId = null,
		int $maxFileCount = 1000,
		bool $loop = true
	) {
		if ($startFileId && !$startFileName) {
			throw new ValidationException('$startFileName is required if $startFileId is provided.');
		}

		$files = [];

		while (true) {
			$response = $this->_listFileVersions($bucketId, $prefix, $delimiter, $startFileName, $startFileId, $maxFileCount);

			if (!$loop) {
				return $response;
			}

			array_merge($files, $response['files']);
			$startFileName = $response['nextFileName'];
			$startFileId   = $response['nextFileId'];

			if ($startFileName == null && $startFileId == null) {
				break;
			}
		}

		return [
			'files'        => $files,
			'nextFileName' => null,
			'nextFileId'   => null,
		];
	}

	/**
	 * Internal method to call the b2_list_file_versions API
	 * 
	 * @see Client::listFileVersions()
	 * 
	 * @return iterable<File>
	 */
	private function _listFileVersions($bucketId, $prefix, $delimiter, $startFileName, $startFileId, $maxFileCount): iterable
	{
		$json = [
			'bucketId'     => $bucketId,
			'maxFileCount' => $maxFileCount,
		];

		if ($startFileName) {
			$json['startFileName'] = $startFileName;
		}

		if ($startFileId) {
			$json['startFileId'] = $startFileId;
		}

		if ($prefix) {
			$json['prefix'] = $prefix;
		}

		if ($delimiter) {
			$json['delimiter'] = $delimiter;
		}

		$response = $this->client->request('POST', '/b2_list_file_versions', [
			'json' => $json
		]);

		/*
		$files = [];

		foreach ($response['files'] as $file) {
			$files[] = new File($file, true);
		}

		$response['files'] = $files;

		return $response;
		*/

		return File::iterableFromArray(json_decode((string) $response->getBody())->files);
	}

	public function getFileById(string $bucketId, string $fileId): File
	{
		$files = $this->_listFileVersions($bucketId, '', null, null, $fileId, 1);

		if (iterator_count($files) < 1) {
			throw new Exception();
		}

		return File::fromArray($files[0]);
	}

	public function getFileByName(string $bucketId, string $fileName): File
	{
		$files = $this->_listFileNames($bucketId, '', null, $fileName, 1);

		if (iterator_count($files) < 1) {
			throw new Exception();
		}

		return File::fromArray($files[0]);
	}

	/**
	 * Lists application keys associated with an account.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_list_keys.html
	 * 
	 * @param string $startApplicationKeyId The first key to return. Used when a query hits the `maxKeyCount`, and you
	 *                                      want to get more.
	 * @param int    $maxKeyCount           The maximum number of keys to return in the response. The default value is
	 *                                      1000, and the maximum is 10000. The maximum number of keys returned per
	 *                                      transaction is 1000.
	 *                                      If more than 1000 are returned, the call will be billed as multiple
	 *                                      transactions.
	 * @param bool   $loop                  Make API requests until there are no keys left.
	 * 
	 * @return array
	 */
	public function listKeys(string $startApplicationKeyId = null, int $maxKeyCount = 1000, bool $loop = true)
	{
		//$keys = [];

		while (true) {
			$response = $this->_listKeys($startApplicationKeyId, $maxKeyCount);

			if (!$loop) {
				return $response;
			}

			//array_merge($keys, $response['keys']);
			$startApplicationKeyId = $response['nextApplicationKeyId'];

			if ($startApplicationKeyId == null) {
				break;
			}
		}

		return [
			'keys'                 => Key::iterableFromArray($response['keys']),
			'nextApplicationKeyId' => null,
		];
	}

	/**
	 * Internal method to call the b2_list_keys API
	 * 
	 * @see Client::listKeys()
	 */
	private function _listKeys($startApplicationKeyId, $maxKeyCount)
	{
		$json = [
			'maxKeyCount' => $maxKeyCount,
		];

		if ($startApplicationKeyId) {
			$json['startApplicationKeyId'] = $startApplicationKeyId;
		}

		$response = $this->client->request('POST', '/b2_list_keys', [
			'json' => $json,
		]);

		return $response;
	}

	/**
	 * Lists the parts that have been uploaded for a large file that has not been finished yet. 
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_list_parts.html
	 * 
	 * @param string $fileId          The ID returned by b2_start_large_file. This is the file whose parts will be
	 *                                listed.
	 * @param int    $startPartNumber The first part to return. Used when a query hits the maxKeyCount, and you want to
	 *                                get more.
	 * @param int    $maxPartCount    The maximum number of parts to return in the response. The default value is 1000,
	 *                                and the maximum is 10000. The maximum number of parts returned per transaction
	 *                                is 1000.
	 *                                If more than 1000 are returned, the call will be billed as multiple transactions.
	 * @param bool   $loop            Make API requests until there are no keys left.
	 * 
	 * @return array
	 */
	public function listParts(string $fileId, int $startPartNumber = null, int $maxPartCount = 1000, $loop = true)
	{
		$parts = [];

		while (true) {
			$response = $this->_listParts($fileId, $startPartNumber, $maxPartCount);

			if (!$loop) {
				return $response;
			}

			array_merge($parts, $response['parts']);
			$startPartNumber = $response['nextPartNumber'];

			if ($startPartNumber == null) {
				break;
			}
		}

		return [
			'parts'          => $parts,
			'nextPartNumber' => null,
		];
	}

	/**
	 * Internal method to call the b2_list_parts API
	 * 
	 * @see Client::listParts()
	 * 
	 * @return array
	 */
	private function _listParts($fileId, $startPartNumber, $maxPartCount)
	{
		$json = [
			'fileId' => $fileId
		];

		if ($startPartNumber) {
			$json['startpartNumber'] = $startPartNumber;
		}

		if ($maxPartCount) {
			$json['maxPartCount'] = $maxPartCount;
		}

		$response = $this->client->request('POST', '/b2_list_parts', [
			'json' => $json,
		]);

		return $response;
	}

	/**
	 * Lists information about large file uploads that have been started, but have not been finished or canceled.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_list_unfinished_large_files.html
	 * 
	 * @param string $bucketId     The bucket to look for file names in.
	 * @param string $namePrefix   Only files whose names match the prefix will be returned.
	 * @param string $startFileId  The first upload to return.
	 * @param int    $maxFileCount The maximum number of files to return from this call. The default value is 100, and
	 *                             the maximum allowed is 100. 
	 * @param bool   $loop         Make API requests until there are no files left.
	 * 
	 * @return array
	 */
	public function listUnfinishedLargeFiles(
		string $bucketId,
		string $namePrefix = null,
		string $startFileId = null,
		int $maxFileCount = 100,
		bool $loop = true
	) {

		$files = [];

		while (true) {
			$response = $this->_listUnfinishedLargeFiles($bucketId, $namePrefix, $startFileId, $maxFileCount);

			if (!$loop) {
				return $response;
			}

			array_merge($files, $response['files']);

			$startFileId = $response['nextFileId'];

			if ($startFileId == null) {
				break;
			}
		}

		return [
			'files'      => $files,
			'nextFileId' => null,
		];
	}

	/**
	 * Internal method to call the b2_list_unfinished_large_files API
	 * 
	 * @see Client::listUnfinishedLargeFiles()
	 * 
	 * @return array
	 */
	private function _listUnfinishedLargeFiles($bucketId, $namePrefix, $startFileId, $maxFileCount)
	{
		$json = [
			'bucketId'     => $bucketId,
			'maxFileCount' => $maxFileCount,
		];

		if ($namePrefix) {
			$json['namePrefix'] = $namePrefix;
		}

		if ($startFileId) {
			$json['startFileId'] = $startFileId;
		}

		$response = $this->client->request('POST', '/b2_list_unfinished_large_files', [
			'json' => $json,
		]);

		$files = [];

		foreach ($response['files'] as $file) {
			$files[] = File::fromArray(json_decode((string) $response->getBody(), true));
		}

		$response['files'] = $files;

		return $response;
	}

	/**
	 * Prepares for uploading the parts of a large file. 
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_start_large_file.html
	 * 
	 * @param string $bucketId    The ID of the bucket that the file will go in. 
	 * @param string $fileName    The name of the file.
	 * @param string $contentType The MIME type of the content of the file.
	 * @param array  $fileInfo    A JSON object holding the name/value pairs for the custom file info.
	 * 
	 * @return File
	 */
	public function startLargeFile(string $bucketId, string $fileName, string $contentType, array $fileInfo = null)
	{
		$json = [
			'bucketId'    => $bucketId,
			'fileName'    => $fileName,
			'contentType' => $contentType,
		];

		if ($fileInfo) {
			$json['fileInfo'] = $fileInfo;
		}

		$response = $this->client->request('POST', '/b2_start_large_file', [
			'json' => $json,
		]);


		return File::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Updates the type attribute of a bucket by the given ID.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_update_bucket.html
	 *
	 * @param string           $bucketId       The unique ID of the bucket.
	 * @param string           $bucketType     Either "allPublic", meaning that files in this bucket can be downloaded
	 *                                         by anybody, or "allPrivate", meaning that you need a bucket
	 *                                         authorization token to download the files. 
	 * @param array            $bucketInfo     User-defined information to be stored with the bucket.
	 * @param CORSRule[]       $corsRules      The initial CORS rules for this bucket.
	 * @param LifecycleRule[]  $lifecycleRules The initial lifecycle rules for this bucket.
	 * @param int              $ifRevisionIs   When set, the update will only happen if the revision number stored in
	 *                                         the B2 service matches the one passed in.
	 * 
	 * @return Bucket
	 * 
	 * @throws ValidationException
	 */
	public function updateBucket(
		string $bucketId,
		string $bucketType = null,
		array $bucketInfo = null,
		$corsRules = null,
		$lifecycleRules = null,
		$ifRevisionIs = null
	) {
		$json = [
			'accountId'  => $this->accountAuthorization->getAccountId(),
			'bucketId'   => $bucketId,
		];

		if ($bucketType) {
			if (!in_array($bucketType, [BucketType::PUBLIC, BucketType::PRIVATE])) {
				throw new ValidationException(sprintf(
					'Bucket type must be "%s" or "%s"',
					BucketType::PRIVATE,
					BucketType::PUBLIC
				));
			}

			$json['bucketType'] = $bucketType;
		}

		if ($bucketInfo) {
			$json['bucketInfo'] = $bucketInfo;
		}

		if ($corsRules) {
			$json['corsRules'] = $corsRules;
		}

		if ($lifecycleRules) {
			$json['lifecycleRules'] = $lifecycleRules;
		}

		if ($ifRevisionIs) {
			$json['ifRevisionIs'] = $ifRevisionIs;
		}

		$response = $this->client->request('POST', '/b2_update_bucket', [
			'json' => $json
		]);

		return Bucket::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Uploads a file to a bucket and returns a File object.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_upload_file.html
	 *
	 * @param string|resource $body         The file to be uploaded. String or stream resource.
	 * @param string          $bucketId     The ID of the bucket to upload the file to.
	 * @param string          $fileName     The name of the file.
	 * @param string          $contentType  The MIME type of the content of the file.
	 * 
	 * @return File
	 */
	public function uploadFile(
		$body,
		string $bucketId,
		string $fileName,
		string $contentType = File::CONTENT_TYPE_AUTO,
		array $fileInfo = null
	): File {
		$uploadMetadata = $this->getUploadMetadata($body);
		$uploadEndpoint = $this->getUploadUrl($bucketId);

		$headers = [
			'Authorization'     => $uploadEndpoint['authorizationToken'],
			'Content-Type'      => $contentType,
			'Content-Length'    => $uploadMetadata['contentLength'],
			'X-Bz-File-Name'    => urlencode($fileName),
			'X-Bz-Content-Sha1' => $uploadMetadata['contentSha1'],
		];

		foreach ($fileInfo as $key => $info) {
			$headers['X-Bz-Info-' . $key] = rawurlencode($info);
		}

		$response = $this->client->request('POST', $uploadEndpoint['uploadUrl'], [
			'headers' => $headers,
			'body'    => $body
		]);

		return File::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Uploads one part of a large file to B2
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_upload_part.html
	 * 
	 * @param string|resource $body       The file part to be uploaded. String or stream resource.
	 * @param string          $fileId     The ID of the large file whose parts you want to upload.
	 * @param int             $partNumber The parts uploaded for one file must have contiguous numbers, starting with 1.
	 */
	public function uploadPart($body, string $fileId, int $partNumber): File
	{
		$uploadMetadata = $this->getUploadMetadata($body);
		$uploadEndpoint = $this->getUploadPartUrl($fileId);

		$response = $this->client->request('POST', $uploadEndpoint['uploadUrl'], [
			'headers' => [
				'Authorization'     => $$uploadEndpoint['uploadAuthorization'],
				'Content-Length'    => $uploadMetadata['contentLength'],
				'X-Bz-Part-Number'  => $partNumber,
				'X-Bz-Content-Sha1' => $uploadMetadata['contentSha1'],
			],
			'body' => $body
		]);

		return File::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Get the capabilities, bucket restrictions, and prefix restrictions.
	 */
	public function getAllowed(): array
	{
		return $this->allowed;
	}

	/**
	 * The recommended part size for each part of a large file. It is recommended to use this part size for optimal
	 * performance.
	 */
	public function getRecommendedPartSize(): int
	{
		return $this->recommendedPartSize;
	}

	/**
	 * The smallest possible size of a part of a large file (except the last one). Upload performance may be impacted
	 * if you use this value.
	 */
	public function getAbsoluteMinimumPartSize(): int
	{
		return $this->absoluteMinimumPartSize;
	}

	/**
	 * Calculate the hash and content length of a string or stream
	 * 
	 * @param string|resource $content The resource used to calculate a size in bytes and SHA1 hash.
	 */
	protected function getUploadMetadata($content): array
	{
		$size = null;
		$hash = null;

		if (is_resource($content)) {
			// We need to calculate the file's hash incrementally from the stream.
			$context = hash_init('sha1');
			hash_update_stream($context, $content);
			$hash = hash_final($context);

			// Similarly, we have to use fstat to get the size of the stream.
			$size = fstat($content)['size'];

			// Rewind the stream before passing it to the HTTP client.
			rewind($content);
		} else {
			// We've been given a simple string body, it's super simple to calculate the hash and size.
			$hash = sha1($content);
			$size = mb_strlen($content);
		}

		return [
			'contentLength' => $size,
			'contentSha1'   => $hash,
		];
	}
}
