## Backblaze B2 SDK for PHP
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

`b2-sdk-php` is a client library for working with Backblaze's B2 storage service.

## Documentation

[Full documentation of the Backblaze B2 API can be found here.](https://www.backblaze.com/b2/docs/index.html)

## Examples

```php
use Zaxbux\BackblazeB2\Client;
use Zaxbux\BackblazeB2\Bucket;

$client = new Client('accountId', 'applicationKey');

// Returns a Bucket object.
$bucket = $client->createBucket([
	'BucketName' => 'my-special-bucket',
	'BucketType' => Bucket::TYPE_PRIVATE // or TYPE_PUBLIC
]);

// Change the bucket to private. Also returns a Bucket object.
$updatedBucket = $client->updateBucket([
	'BucketId'   => $bucket->getId(),
	'BucketType' => Bucket::TYPE_PUBLIC
]);

// Retrieve an array of Bucket objects on your account.
$buckets = $client->listBuckets();

// Delete a bucket.
$client->deleteBucket([
	'bucketId' => 'xxxxxxxxxxxxxxxxxxxxxxxx'
]);

// Upload a file to a bucket. Returns a File object.
$file = $client->upload([
	'bucketName' => 'my-special-bucket',
	'fileName' => 'path/to/upload/to',
	'body' => 'I am the file content'

	// The file content can also be provided via a resource.
	// 'body' => fopen('/path/to/input', 'r')
]);

// Download a file from a bucket. Returns the file content.
$fileContent = $client->download([
	'fileId' => $file->getId()

	// Can also identify the file via bucket and path:
	// 'BucketName' => 'my-special-bucket',
	// 'FileName' => 'path/to/file'

	// Can also save directly to a location on disk. This will cause download() to not return file content.
	// 'SaveAs' => '/path/to/save/location'
]);

// Delete a file from a bucket. Returns true or false.
$fileDelete = $client->deleteFile([
	'fileId' => $file->getId()
	
	// Can also identify the file via bucket and path:
	// 'BucketName' => 'my-special-bucket',
	// 'FileName' => 'path/to/file'
]);

// Retrieve an array of file objects from a bucket.
$fileList = $client->listFiles([
	'bucketId' => 'xxxxxxxxxxxxxxxxxxxxxxxx'
]);
```

## Installation

Installation is via Composer:

```bash
$ composer require zaxbux/b2-sdk-php
```

## Tests

Tests are run with PHPUnit. After installing PHPUnit via Composer:

```bash
$ vendor/bin/phpunit
```

## Authorization Cache

If you want to cache the authorization token to reduce the number of API calls, create a class that implements `Zaxbux\BackblazeB2\AuthCacheInterface`.

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

## Contributors

Feel free to contribute in any way you can whether that be reporting issues, making suggestions or sending PRs.

## License

MIT
