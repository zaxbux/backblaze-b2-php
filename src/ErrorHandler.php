<?php

namespace Zaxbux\BackblazeB2;

use Zaxbux\BackblazeB2\Exception\B2Exception;
use Zaxbux\BackblazeB2\Exception\BadJsonException;
use Zaxbux\BackblazeB2\Exception\BadValueException;
use Zaxbux\BackblazeB2\Exception\BucketAlreadyExistsException;
use Zaxbux\BackblazeB2\Exception\NotFoundException;
use Zaxbux\BackblazeB2\Exception\FileNotPresentException;
use Zaxbux\BackblazeB2\Exception\BucketNotEmptyException;
use GuzzleHttp\Psr7\Response;

class ErrorHandler {
	protected static $errorCodes = [
		'bad_json'                       => BadJsonException::class,
		'bad_value'                      => BadValueException::class,
		'bad_request'                    => BadRequestException::class,
		'bad_auth_token'                 => BadAuthTokenException::class,
		'bad_bucket_id'                  => BadBucketIdException::class,
		'expired_auth_token'             => ExpiredAuthException::class,
		'duplicate_bucket_name'          => BucketAlreadyExistsException::class,
		'not_found'                      => NotFoundException::class,
		'file_not_present'               => FileNotPresentException::class,
		'cannot_delete_non_empty_bucket' => BucketNotEmptyException::class,
		'transaction_cap_excceded'       => TransactionCapExceededException::class,
		'cap_excceded'                   => CapExceededException::class,
		'unauthorized'                   => UnauthorizedException::class,
		'unsupported'                    => UnsupportedException::class,
		'range_not_satisfiable'          => RangeNotSatisfiableException::class,
		'too_many_buckets'               => TooManyBucketsException::class,
		'duplicate_bucket_name'          => DuplicateBucketNameException::class,
	];

	public static function handleErrorResponse(Response $response) {
		$responseJson = json_decode($response->getBody(), true);

		if (isset(self::$errorCodes[$responseJson['code']])) {
			$exceptionClass = self::$errorCodes[$responseJson['code']];
		} else {
			// We don't have an exception mapped to this response error, throw generic exception
			$exceptionClass = B2Exception::class;
		}

		throw new $exceptionClass('B2 API error: '.$responseJson['message'], $responseJson['code']);
	}
}
