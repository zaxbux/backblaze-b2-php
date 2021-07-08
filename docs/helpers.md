# Helpers

A number of helper methods are available to make calling API operations simpler.

## Application Key Helpers

```php
// Create a new key.
$client->applicationKey()->create($keyName, $capabilities, $validDuration, $bucketId, $namePrefix);

// List keys.
$client->applicationKey()->list($startApplicationKeyId, $maxKeyCount);

// List all keys.
$client->applicationKey()->listAll($startApplicationKeyId);

// Delete a key.
$client->applicationKey(Key|'applicationKeyId')->delete();
```

## Bucket Helpers

```php
// Create a new bucket.
$client->bucket()->create($name, $type, $info, $corsRules, $lifecycleRules, $fileLockEnabled, $defaultSSE);

// List all buckets in the account.
$client->bucket()->listAll($types);

// Get a bucket by name or ID.
$client->bucket()->getById('bucketId');
$client->bucket()->getByName('bucketName');

// Update a bucket.
$client->bucket(Bucket|'bucketId')->update($name, $type, $info, $corsRules, $lifecycleRules, $fileLockEnabled, $defaultSSE);

// Delete a bucket.
$client->bucket(Bucket|'bucketId')->delete();

// List files
$client->bucket(Bucket|'bucketId')->listFileNames();
$client->bucket(Bucket|'bucketId')->listFileVersions();
$client->bucket(Bucket|'bucketId')->listAllFileNames();
$client->bucket(Bucket|'bucketId')->listAllFileVersions();
```

## File Helpers

```php

// Get information on a file.
$client->file()->getInfo('fileId');

// Get a file by ID.
$client->file()->getById('fileId');

// Get a file by name.
$client->file()->getByName('fileName');

// Download a file.
$client->file(File|'fileId')->download($options, $sink, $headersOnly);

// Copy a file.
$client->file(File|'fileId')->copy(
	$fileName,
	$destinationBucketId,
	$range,
	$metadataDirective,
	$contentType,
	$fileInfo,
	$fileRetention,
	$legalHold,
	$sourceSSE,
	$destinationSSE
);

// Hide a file.
$client->file(File|'fileId')->hide();

// Update the legal hold for a file.
$client->file(File|'fileId')->updateLegalHold($legalHold);

// Update the retention settings for a file.
$client->file(File|'fileId')->updateRetention($retention, $bypassGovernance);

// Delete one file version.
$client->file(File|'fileId')->deleteVersion($bypassGovernance);

// Delete all versions of a file.
$client->file(File|'fileId')->deleteAllVersions($bypassGovernance);
```

## Upload Helpers

```php
use Zaxbux\BackblazeB2\Helpers\UploadHelper;

$helper = UploadHelper::instance($client);

// These methods automatically decide how to upload, based on file size.
$helper->uploadStream();
$helper->uploadFile();

// This method implements the entire large file upload process.
$helper->uploadLargeFile();