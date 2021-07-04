<?php

namespace Zaxbux\BackblazeB2\Http;

use Psr\Http\Message\ResponseInterface;
use Zaxbux\BackblazeB2\Client\Exception;

class ErrorHandler {
	protected static $errorCodes = [
		'bad_request'                    => Exception\BadRequestException::class,
		'bad_auth_token'                 => Exception\BadAuthTokenException::class,
		'bad_bucket_id'                  => Exception\BadBucketIdException::class,
		'expired_auth_token'             => Exception\ExpiredAuthException::class,
		'not_found'                      => Exception\NotFoundException::class,
		'file_not_present'               => Exception\FileNotPresentException::class,
		'transaction_cap_exceeded'       => Exception\TransactionCapExceededException::class,
		'cap_exceeded'                   => Exception\CapExceededException::class,
		'unauthorized'                   => Exception\UnauthorizedException::class,
		'unsupported'                    => Exception\UnsupportedException::class,
		'range_not_satisfiable'          => Exception\RangeNotSatisfiableException::class,
		'too_many_buckets'               => Exception\TooManyBucketsException::class,
		'duplicate_bucket_name'          => Exception\DuplicateBucketNameException::class,
	];

	public static function getException(ResponseInterface $response) {
		$responseJson = json_decode($response->getBody(), true);

		if (isset(self::$errorCodes[$responseJson['code']])) {
			$exceptionClass = self::$errorCodes[$responseJson['code']];
		} else {
			// We don't have an exception mapped to this response error, throw generic exception
			$exceptionClass = Exception\B2APIException::class;
		}

		return new $exceptionClass('B2 API error: '.$responseJson['message'], $responseJson['code']);
	}
}
