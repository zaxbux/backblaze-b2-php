<?php
declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Http\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zaxbux\BackblazeB2\Config;
use Zaxbux\BackblazeB2\Http\StatusCode;

/**
 * Middleware that will retry a request if necessary.
 * 
 * @package BackblazeB2\Http\Middleware
 * @author  Zachary Schneider <hello@zacharyschneider.ca>
 * @license MIT
 */
class RetryMiddleware
{
    protected const RETRY_STATUS_CODES = [
        StatusCode::HTTP_TOO_MANY_REQUESTS,
        StatusCode::HTTP_SERVICE_UNAVAILABLE
    ];

    /** @var \Zaxbux\BackblazeB2\Config */
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function __invoke($handler)
    {
        return new \GuzzleHttp\RetryMiddleware(static::retryDecider($this->config), $handler, static::retryDelay($this->config));
    }

    /**
     * Creates an anonymous function that decides if a request should be retried.
     *
     * @param Config $config
	 * 
     * @return callable
     */
    private static function retryDecider(Config $config): callable
    {
        return static function (
            int $retries,
            RequestInterface $request,
            ResponseInterface $response = null,
            $exception = null
        ) use ($config): bool {
            // Only retry allowed status codes.
            if (!in_array($response->getStatusCode(), static::RETRY_STATUS_CODES)) {
                return false;
            }

            // Do not retry more than the configured maximum.
            if ($retries >= $config->maxRetries()) {
                return false;
            }

            // Do not retry `b2_authorize_account` requests.
            if (preg_match('/b2_authorize_account$/', rtrim($request->getUri()->getPath(), '/'))) {
                return false;
            }

            return true;
        };
    }

    /**
     * Creates an anonymous function that returns a time,
     * in milliseconds, to wait before making another attempt.
     *
     * @param Config $config
     * @return callable
     */
    private static function retryDelay(Config $config): callable
    {
        return static function (int $retries, ResponseInterface $response) use ($config): int {
            // Use the value of the `Retry-After` header
            if ($response->getStatusCode() === StatusCode::HTTP_TOO_MANY_REQUESTS) {
                $delay = (int) $response->getHeader('Retry-After')[0] ?? $config->maxRetryDelay();
                return $delay * 1000;
            }

            return \GuzzleHttp\RetryMiddleware::exponentialDelay($retries);
        };
    }
}
