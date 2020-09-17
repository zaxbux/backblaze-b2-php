## Backblaze B2 SDK for PHP
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

`b2-sdk-php` is a client library for working with Backblaze's B2 storage service.

## Documentation

[Full documentation of the Backblaze B2 API can be found here.](https://www.backblaze.com/b2/docs/index.html)

Each method is well documented in [`src/client.php`](src/Client.php).

For examples and options that are specific to this library, please see [`docs/`](docs/):

  * [Authorization](docs/authorization.md)
  * [Downloading](docs/downloading.md)
  * [Uploading](docs/uploading.md)
  * [Large Files](docs/large_files.md)
  * [Buckets](docs/buckets.md)
  * [Files](docs/files.md)
  * [Keys](docs/keys.md)

## Installation

Installation is via Composer:

```bash
$ composer require zaxbux/b2-sdk-php
```

## Getting Started

```php
<?php

use Zaxbux\BackblazeB2\Client;

$accountId      = '...';
$applicationKey = '...';

$client = new Client($accountId, $applicationKey);

// Retrieve an array of Bucket objects on your account.
$buckets = $client->listBuckets();
```

## Authorization Cache

If you want to cache the authorization token to reduce the number of API calls, create a class that implements `Zaxbux\BackblazeB2\AuthCacheInterface`.

```php
<?php

use Zaxbux\BackblazeB2\Client;

$authCache = new AuthorizationCacheExample;

$client = new Client($accountId, $applicationKey, $authCache);

```

### Sample Authorization Cache
```php
<?php

use Zaxbux\BackblazeB2\AuthCacheInterface;

class AuthorizationCacheExample implements AuthCacheInterface {
	public function cache($key, $value) {
		$myCache->remember($key, $value, AuthCacheInterface::EXPIRES)
	}

	public function get($key) {
		$myCache->get($key);
	}
}
```

The `AuthCacheInterface::EXPIRES` constant is how long the authorization token is valid for, in seconds. Currently, this is equivalent to 24 hours. Requests made after the token expires will result in an `ExpiredAuthTokenException` exception being thrown. You will need to get a new authorization token with `authorizeAccount()`.

## Tests

Tests are run with PHPUnit. After installing PHPUnit via Composer:

```bash
$ vendor/bin/phpunit
```

## Contributing

Feel free to contribute in any way by reporting an issue, making a suggestion, or submitting a pull request.

## License

MIT
