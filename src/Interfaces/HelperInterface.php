<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Interfaces;

use Zaxbux\BackblazeB2\Client;

/** @package BackblazeB2\Interfaces */
interface HelperInterface
{
	public function __construct(Client $client);

	public static function instance(Client $client);
}