<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Helpers;

use Zaxbux\BackblazeB2\Object\File\DownloadOptions;
use Zaxbux\BackblazeB2\Response\FileDownload;
use Zaxbux\BackblazeB2\Utils;

class DownloadHelper extends AbstractHelper {
	/**
	 * Internal method to save/stream files.
	 * 
	 * @param string                 $downloadUrl The URL to make the request to.
	 * @param array                  $query       Query parameters.
	 * @param DownloadOptions|array  $options     Additional options for the B2 API.
	 * @param string|resource        $sink        A string, stream, or StreamInterface that specifies where to save the file.
	 * @param bool                   $headersOnly Only get the file headers, without downloading the whole file.
	 */
	public function download(
		string $downloadUrl,
		?array $query = null,
		$options = null,
		$sink = null,
		?bool $headersOnly = false
	): FileDownload {
		if (!$options instanceof DownloadOptions) {
			$options = DownloadOptions::fromArray($options ?? []);
		}

		// Build query string from query parameters and download options.
		$queryString = implode('&', [http_build_query($query ?? []), $options->toQueryString() ?? []]);

		$response = $this->getHttpClient()->request($headersOnly ? 'HEAD' : 'GET', $downloadUrl, [
			'query'   => $queryString,
			'headers' => $options->getHeaders(),
			'sink'    => $sink ?? null,
			'stream'  => Utils::isStream($sink),
		]);

		return FileDownload::create($response, !is_string($sink) ? null : $sink);
	}
}