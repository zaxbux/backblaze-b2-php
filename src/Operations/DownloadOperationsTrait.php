<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Operations;

use Zaxbux\BackblazeB2\Client;
use Zaxbux\BackblazeB2\Utils;
use Zaxbux\BackblazeB2\Helpers\DownloadHelper;
use Zaxbux\BackblazeB2\Http\Endpoint;
use Zaxbux\BackblazeB2\Object\DownloadAuthorization;
use Zaxbux\BackblazeB2\Object\File;
use Zaxbux\BackblazeB2\Object\File\DownloadOptions;
use Zaxbux\BackblazeB2\Response\FileDownload;

/** @package BackblazeB2\Operations */
trait DownloadOperationsTrait
{
	/** @var \Zaxbux\BackblazeB2\Config */
	protected $config;
	
	/**
	 * Generates an authorization token that can be used to download files
	 * with the specified prefix from a private B2 bucket.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_get_download_authorization.html
	 * 
	 * @b2-capability shareFiles
	 * @b2-transaction Class C
	 * 
	 * @param string                 $bucketId       The identifier for the bucket.
	 * @param string                 $fileNamePrefix The file name prefix of files the download authorization token will allow access.
	 * @param int                    $validDuration  The number of seconds before the authorization token will expire. The minimum
	 *                                               value is `1` second. The maximum value is `604800`. Default: `604800`.
	 * @param DownloadOptions|array  $options        Additional options to pass to the API.
	 */
	public function getDownloadAuthorization(
		string $fileNamePrefix,
		?string $bucketId = null,
		?int $validDuration = DownloadAuthorization::VALID_DURATION_MAX,
		$options = null
	): DownloadAuthorization {
		if (!$options instanceof DownloadOptions) {
			$options = DownloadOptions::fromArray($options ?? []);
		}

		$response = $this->http->request('POST', Endpoint::GET_DOWNLOAD_AUTHORIZATION, [
			'json' => Utils::filterRequestOptions([
				File::ATTRIBUTE_BUCKET_ID        => $bucketId ?? $this->getAllowedBucketId(),
				File::ATTRIBUTE_FILE_NAME_PREFIX => $fileNamePrefix,
				File::ATTRIBUTE_VALID_DURATION   => $validDuration,
			], $options->getAuthorizationOptions()),
		]);

		return DownloadAuthorization::fromResponse($response);
	}

	/**
	 * Downloads one file from B2 by File ID.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_download_file_by_id.html
	 * 
	 * @b2-capability [readFiles] If the bucket is private.
	 * @b2-transaction Class B
	 * 
	 * @param string                $fileId      The file ID to download.
	 * @param DownloadOptions|array $options     An optional array of additional B2 API options.
	 * @param string                $range       A standard RFC 7233 byte-range request, that will only return part of the stored file.
	 * @param string|resource       $sink        A string, stream, or `StreamInterface` that specifies where to save the file.
	 *                                           {@link https://docs.guzzlephp.org/en/stable/request-options.html#sink}
	 * @param bool                  $headersOnly Only get the file headers, without downloading the whole file.
	 */
	public function downloadFileById(
		string $fileId,
		$options = null,
		$sink = null,
		?bool $headersOnly = false
	): FileDownload {
		return DownloadHelper::instance($this)->download(
			Utils::joinPaths(
				$this->accountAuthorization()->getDownloadUrl(),
				Client::B2_API_VERSION,
				Endpoint::DOWNLOAD_FILE_BY_ID
			),
			[File::ATTRIBUTE_FILE_ID => $fileId],
			$options,
			$sink,
			$headersOnly
		);
	}

	/**
	 * Downloads one file from B2 by File Name.
	 * 
	 * @b2-capability [readFiles] If the bucket is private.
	 * @b2-transaction Class B
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_download_file_by_name.html
	 * 
	 * @param string                 $fileName    The file name to download.
	 * @param string                 $bucketName  The bucket the file is contained in.
	 * @param DownloadOptions|array  $options     An optional array of additional B2 API options.
	 * @param string                 $range       A standard RFC 7233 byte-range request, that will only return part of the stored file.
	 * @param string|resource        $sink        A string, stream, or `StreamInterface` that specifies where to save the file.
	 *                                            {@link https://docs.guzzlephp.org/en/stable/request-options.html#sink}
	 * @param bool                   $headersOnly Only get the file headers, without downloading the whole file.
	 */
	public function downloadFileByName(
		string $fileName,
		?string $bucketName =  null,
		$options = null,
		$sink = null,
		?bool $headersOnly = false
	): FileDownload {
		return DownloadHelper::instance($this)->download(
			Utils::joinPaths(
				$this->accountAuthorization()->getApiUrl(),
				'file',
				$bucketName ?? $this->getAllowedBucketName(),
				$fileName
			),
			null,
			$options,
			$sink,
			$headersOnly
		);
	}
}