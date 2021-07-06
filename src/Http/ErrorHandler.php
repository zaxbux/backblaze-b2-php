<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Http;

use JsonException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zaxbux\BackblazeB2\Exceptions\Request\{
	B2APIException,
	BadRequestException,
	TooManyBucketsException,
	DuplicateBucketNameException,
	FileNotPresentException,
	OutOfRangeException,
	InvalidBucketIdException,
	InvalidFileIdException,
	UnsupportedException,
	UnauthorizedException,
	BadAuthTokenException,
	ExpiredAuthTokenException,
	AccessDeniedException,
	CapExceededException,
	StorageCapExceededException,
	TransactionCapExceededException,
	DownloadCapExceededException,
	NotFoundException,
	MethodNotAllowedException,
	RequestTimeoutException,
	ConflictException,
	RangeNotSatisfiableException,
	ServiceUnavailableException,
};

class ErrorHandler
{
	private const ERROR_CODES = [
		Response::HTTP_BAD_REQUEST => [
			'bad_request' => BadRequestException::class,
			'too_many_buckets' => TooManyBucketsException::class,
			'duplicate_bucket_name' => DuplicateBucketNameException::class,
			'file_not_present' => FileNotPresentException::class,
			'out_of_range' => OutOfRangeException::class,
			'invalid_bucket_id' => InvalidBucketIdException::class,
			'bad_bucket_id' => InvalidBucketIdException::class,
			'invalid_file_id' => InvalidFileIdException::class,
		],
		Response::HTTP_UNAUTHORIZED => [
			'unsupported' => UnsupportedException::class,
			'unauthorized' => UnauthorizedException::class,
			'bad_auth_token' => BadAuthTokenException::class,
			'expired_auth_token' => ExpiredAuthTokenException::class,
			'access_denied' => AccessDeniedException::class,
		],
		Response::HTTP_FORBIDDEN => [
			'cap_exceeded' => CapExceededException::class,
			'storage_cap_exceeded' => StorageCapExceededException::class,
			'transaction_cap_exceeded' => TransactionCapExceededException::class,
			'access_denied' => AccessDeniedException::class,
			'download_cap_exceeded' => DownloadCapExceededException::class,
		],
		Response::HTTP_NOT_FOUND => ['not_found' => NotFoundException::class,],
		Response::HTTP_METHOD_NOT_ALLOWED => ['method_not_allowed' => MethodNotAllowedException::class,],
		Response::HTTP_REQUEST_TIMEOUT => ['request_timeout' => RequestTimeoutException::class,],
		Response::HTTP_CONFLICT => ['conflict' => ConflictException::class,],
		Response::HTTP_RANGE_NOT_SATISFIABLE => ['range_not_satisfiable' => RangeNotSatisfiableException::class,],
		Response::HTTP_SERVICE_UNAVAILABLE => [
			'service_unavailable' => ServiceUnavailableException::class,
			'bad_request' => BadRequestException::class,
		],
	];

	public static function getException(RequestInterface $request, ResponseInterface $response): B2APIException
	{
		$exceptionClass = B2APIException::class;

		try {
			$responseJson = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

			$statusCode = $response->getStatusCode();
			$errorCode  = $responseJson['code'];

			if (array_key_exists($statusCode, static::ERROR_CODES)) {
				/** @var string */
				$exceptionClass = static::ERROR_CODES[$statusCode][$errorCode] ?? B2APIException::class;
			}
		} catch (JsonException $ex) {
			// Ignore JSON exceptions, response object is available instead
		}

		return new $exceptionClass($request, $response);
	}
}
