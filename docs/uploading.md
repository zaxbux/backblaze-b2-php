# Uploading

[Backblaze's Uploading Documentation](https://www.backblaze.com/b2/docs/uploading.html)

## Large Files

For uploading large files, see [`large_files.md`](large_files.md).

## Uploading

See [b2_upload_file](https://www.backblaze.com/b2/docs/b2_upload_file.html).

This method can be used for uploading a single file (under 5GB).

```php
$fileName = '...';

$body = 'File contents.';
// or
$body = fopen('/path/to/file', 'r');

$file = $client->uploadFile($body, $fileName);
```