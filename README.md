## Backblaze B2 SDK for PHP
[![GitHub](https://img.shields.io/github/license/zaxbux/backblaze-b2-php)][licence] [![Packagist Version](https://img.shields.io/packagist/v/zaxbux/backblaze-b2-php)][packagist]

`backblaze-b2-php` is a client library for working with the B2 cloud storage service from Backblaze.

## Documentation

[Full documentation of the Backblaze B2 API can be found here.][b2-docs]

Complete documentation for this library does not exist yet, however most methods are already documented in the source code.

## Installation

Installation is via Composer:

```bash
$ composer require zaxbux/backblaze-b2-php
```

## Getting Started

```php
<?php

use Zaxbux\BackblazeB2\Client;

$accountId      = '...';
$applicationKey = '...';

$client = new Client([$accountId, $applicationKey]);

// Retrieve an array of Bucket objects on your account.
$buckets = $client->listBuckets();
```

## Authorization Cache

If you want to cache the authorization token to reduce the number of API calls, create a class that implements `Zaxbux\BackblazeB2\Interfaces\AuthorizationCacheInterface`.

```php
<?php

use Zaxbux\BackblazeB2\Client;

$authCache = new AuthorizationCacheExample;

$client = new Client(new Config($accountId, $applicationKey, [
  'authorizationCache' => $authCache,
]));

```

### Sample Authorization Cache
```php
<?php

use Zaxbux\BackblazeB2\Interfaces\AuthorizationCacheInterface;

class AuthorizationCacheExample implements AuthorizationCacheInterface {
	public function cache($key, $value) {
		$myCache->remember($key, $value, AuthorizationCacheInterface::EXPIRES)
	}

	public function get($key) {
		$myCache->get($key);
	}
}
```

The `AuthorizationCacheInterface::EXPIRES` constant is how long the authorization token is valid for, in seconds. Currently, this is equivalent to 24 hours. Requests made after the token expires will result in an `ExpiredAuthTokenException` exception being thrown. You will need to get a new authorization token with `authorizeAccount()`.

## Tests

Tests are run with PHPUnit. After installing PHPUnit via Composer:

```bash
$ vendor/bin/phpunit
```

## Contributing

Feel free to contribute in any way by reporting an issue, making a suggestion, or submitting a pull request.

## Licence

[MIT][licence]

[b2-docs]: https://www.backblaze.com/b2/docs/index.html
[licence]: LICENCE.md
[packagist]: https://packagist.org/packages/zaxbux/backblaze-b2-php