# Downloading

This section explains how to download files.


## Calling the API

### Download Authorization

See [b2_get_download_authorization](https://www.backblaze.com/b2/docs/b2_get_download_authorization.html).

To generate an authorization token for a private bucket, use the `getDownloadAuthorization()` method.

```php
$authorization = $client->getDownloadAuthorization($bucketId, $fileNamePrefix, $validDuration, $options);
```

There are two ways to download a file, by ID or by name. `downloadFileById()` and `downloadFileByName()` both return an array containing the response headers and the file stream.

### Downloading a File by ID

See [b2_download_file_by_id](https://www.backblaze.com/b2/docs/b2_download_file_by_id.html).

This is the simplest way to download a file as only the file ID is needed.

```php
$fileId = '...';

$file = $client->downloadFileById($fileId);
```

### Downloading a File by Name

See [b2_download_file_by_name](https://www.backblaze.com/b2/docs/b2_download_file_by_name.html).

You must provide the name of the file and the name of the bucket the file is in.

```php
$fileName   = '...';
$bucketName = '...';

$file = $client->downloadFileByName($fileName, $bucketName);

```

## File Headers

You can get information about a file without having to download it by setting `$headersOnly` to `true`.

```php
$file = $client->downloadFileById('...', [], null, null, true);
// or
$file = $client->downloadFileByName('...', '...', [], null, null, true);

$info = $file['headers']
```

## File Contents

There are a few ways to get the contents of the file you're downloading.

### String

```php

$file->getContents();

```

### Save Directly to Disk

Specify the path to a file that will store the contents of the response body.

```php
$client->downloadFileById('...', [], '', '/path/to/file');
```

### PHP Stream

Write the response to a PHP stream.

```php
$resource = fopen('/path/to/file', 'w');

$file = $client->downloadFileById('...', [], '', $resource);
```

### PSR-7 Stream

Stream the response body to an open PSR-7 stream.

```php
$resource = fopen('/path/to/file', 'w');

$stream = \GuzzleHttp\Psr7\stream_for($resource);

$file = $client->downloadFileById('...', [], '', $stream);
```

### Stream Response

Stream a response rather than download it all up-front.

```php
$options = [
	'stream' => true,
];

$file = $client->downloadFileById('...', $options);

$body = $file['stream'];

while (!$body->eof()) {
    echo $body->read(1024);
}
```

## Options

### Range

To download certain parts of a file, pass a value for the `Range` header:

```php
$range = 'bytes=0-1024';

$file = $client->downloadFileById('...', [], $range);
```

### Overriding Response Headers

To override certain response headers, e.g. `Content-Disposition`, provide an array of options:

```php
$options = [
	'b2ContentDisposition' => '...',
	'b2ContentLanguage'    => '...',
	// ...
]

$file = $client->downloadFileById('...', $options);
```