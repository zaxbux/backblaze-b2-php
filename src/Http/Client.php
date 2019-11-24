<?php

namespace Zaxbux\B2\Http;

use Zaxbux\B2\ErrorHandler;
use GuzzleHttp\Client as GuzzleClient;

/**
 * Client wrapper around Guzzle.
 *
 * @package Zaxbux\B2\Http
 */
class Client extends GuzzleClient
{
    /**
     * Sends a response to the B2 API, automatically handling decoding JSON and errors.
     *
     * @param string $method
     * @param null $uri
     * @param array $options
     * @param bool $asJson
     * @param bool $getContents
     * @return mixed|string
     */
    public function request($method, $uri = null, array $options = [], $asJson = true, $getContents = true)
    {
        $response = parent::request($method, $uri, $options);

        if ($response->getStatusCode() !== 200) {
            ErrorHandler::handleErrorResponse($response);
        }

        if ($asJson) {
            return json_decode($response->getBody(), true);
        }

        if(!$getContents) {
            return $response->getBody();
        }

        return $response->getBody()->getContents();
    }
}
