<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Traits;

trait AuthorizationHeaderTrait
{
	/**
	 * Encodes the account application key ID and application key as a Basic Authorization header value.
	 * 
	 * @param string $applicationKeyId The application key ID.
	 * @param string $applicationKey   The application key.
	 * 
	 * @return string An HTTP Basic Authorization header value.
	 */
	protected static function getBasicAuthorization(string $applicationKeyId, string $applicationKey): string {
		return 'Basic ' . base64_encode($applicationKeyId . ':' . $applicationKey);
	}
}