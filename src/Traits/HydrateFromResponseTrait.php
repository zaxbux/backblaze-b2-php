<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Traits;

use Psr\Http\Message\ResponseInterface;
use Zaxbux\BackblazeB2\Utils;

trait HydrateFromResponseTrait
{
	public static function fromResponse(ResponseInterface $response): self
	{
		return static::fromArray(Utils::jsonDecode((string) $response->getBody()));
	}
}