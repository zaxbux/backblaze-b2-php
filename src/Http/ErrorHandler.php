<?php

namespace Zaxbux\BackblazeB2\Http;

use Psr\Http\Message\ResponseInterface;
use Zaxbux\BackblazeB2\Exceptions;

class ErrorHandler {
	protected static $errorCodes = [
		'bad_request'                    => Exceptions\BadRequestException::class,
		'bad_auth_token'                 => Exceptions\BadAuthTokenException::class,
		'bad_bucket_id'                  => Exceptions\BadBucketIdException::class,
		'expired_auth_token'             => Exceptions\ExpiredAuthException::class,
		'not_found'                      => Exceptions\NotFoundException::class,
		'file_not_present'               => Exceptions\FileNotPresentException::class,
		'transaction_cap_exceeded'       => Exceptions\TransactionCapExceededException::class,
		'cap_exceeded'                   => Exceptions\CapExceededException::class,
		'unauthorized'                   => Exceptions\UnauthorizedException::class,
		'unsupported'                    => Exceptions\UnsupportedException::class,
		'range_not_satisfiable'          => Exceptions\RangeNotSatisfiableException::class,
		'too_many_buckets'               => Exceptions\TooManyBucketsException::class,
		'duplicate_bucket_name'          => Exceptions\DuplicateBucketNameException::class,
	];

	public static function getException(ResponseInterface $response) {
		$responseJson = json_decode($response->getBody(), true);

		if (isset(self::$errorCodes[$responseJson['code']])) {
			$exceptionClass = self::$errorCodes[$responseJson['code']];
		} else {
			// We don't have an exception mapped to this response error, throw generic exception
			$exceptionClass = Exceptions\B2APIException::class;
		}

		return new $exceptionClass('B2 API error: '.$responseJson['message'], $responseJson['code']);
	}
}
