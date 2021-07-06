<?php

namespace tests;

use Zaxbux\BackblazeB2\Exceptions\Request\AccessDeniedException;
use Zaxbux\BackblazeB2\Exceptions\Request\BadAuthTokenException;
use Zaxbux\BackblazeB2\Exceptions\Request\BadRequestException;
use Zaxbux\BackblazeB2\Exceptions\Request\CapExceededException;
use Zaxbux\BackblazeB2\Exceptions\Request\ConflictException;
use Zaxbux\BackblazeB2\Exceptions\Request\DownloadCapExceededException;
use Zaxbux\BackblazeB2\Exceptions\Request\DuplicateBucketNameException;
use Zaxbux\BackblazeB2\Exceptions\Request\ExpiredAuthTokenException;
use Zaxbux\BackblazeB2\Exceptions\Request\FileNotPresentException;
use Zaxbux\BackblazeB2\Exceptions\Request\InvalidBucketIdException;
use Zaxbux\BackblazeB2\Exceptions\Request\InvalidFileIdException;
use Zaxbux\BackblazeB2\Exceptions\Request\MethodNotAllowedException;
use Zaxbux\BackblazeB2\Exceptions\Request\NotFoundException;
use Zaxbux\BackblazeB2\Exceptions\Request\OutOfRangeException;
use Zaxbux\BackblazeB2\Exceptions\Request\RangeNotSatisfiableException;
use Zaxbux\BackblazeB2\Exceptions\Request\RequestTimeoutException;
use Zaxbux\BackblazeB2\Exceptions\Request\ServiceUnavailableException;
use Zaxbux\BackblazeB2\Exceptions\Request\StorageCapExceededException;
use Zaxbux\BackblazeB2\Exceptions\Request\TooManyBucketsException;
use Zaxbux\BackblazeB2\Exceptions\Request\TransactionCapExceededException;
use Zaxbux\BackblazeB2\Exceptions\Request\UnauthorizedException;
use Zaxbux\BackblazeB2\Exceptions\Request\UnsupportedException;

class ClientExceptionsTest extends ClientTestBase
{
	protected function clientInit() {
		return [
			'applicationKeyId' => 'testId',
			'applicationKey'   => 'testKey',
			'handler'          => $this->guzzler->getHandlerStack(),
			'maxRetries'       => 1, // 503 errors cause the client to retry with exponential delay
		];
	}

	/**
	 * @dataProvider exceptionDataProvider
	 */
	public function testThrowsException($errorCode, $statusCode, $exceptionClass)
	{
		$this->expectException($exceptionClass);
		$this->expectExceptionCode($errorCode);
		$this->expectExceptionMessage($errorCode);

		$this->guzzler->queueResponse(
			MockResponse::json(['code' => $errorCode, 'message' => $errorCode], $statusCode)
		);

		if ($statusCode === 503) {
			$this->guzzler->queueResponse(
				MockResponse::json(['code' => $errorCode, 'message' => $errorCode], $statusCode)
			);
		}

		$this->client->getHttpClient()->request('POST', '__any__');
	}

	public function exceptionDataProvider(): array
	{
		return [
			['bad_request', 400, BadRequestException::class],
			['too_many_buckets', 400, TooManyBucketsException::class],
			['duplicate_bucket_name', 400, DuplicateBucketNameException::class],
			['file_not_present', 400, FileNotPresentException::class],
			['out_of_range', 400, OutOfRangeException::class],
			['invalid_bucket_id', 400, InvalidBucketIdException::class],
			['bad_bucket_id', 400, InvalidBucketIdException::class],
			['invalid_file_id', 400, InvalidFileIdException::class],

			['unsupported', 401, UnsupportedException::class],
			['unauthorized', 401, UnauthorizedException::class],
			['bad_auth_token', 401, BadAuthTokenException::class],
			['expired_auth_token', 401, ExpiredAuthTokenException::class],
			['access_denied', 401, AccessDeniedException::class],

			['cap_exceeded', 403, CapExceededException::class],
			['storage_cap_exceeded', 403, StorageCapExceededException::class],
			['transaction_cap_exceeded', 403, TransactionCapExceededException::class],
			['access_denied', 403, AccessDeniedException::class],
			['download_cap_exceeded', 403, DownloadCapExceededException::class],

			['not_found', 404, NotFoundException::class],
			
			['method_not_allowed', 405, MethodNotAllowedException::class],
			
			['request_timeout', 408, RequestTimeoutException::class],
			
			['conflict', 409, ConflictException::class],
			
			['range_not_satisfiable', 416, RangeNotSatisfiableException::class],

			['service_unavailable', 503, ServiceUnavailableException::class],
			['bad_request', 503, BadRequestException::class],
		];
	}
}
