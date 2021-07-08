# Files

## Listing File Names

See [`b2_list_file_names`](https://www.backblaze.com/b2/docs/b2_list_file_names.html).

```php
$bucketId      = '...';
$prefix        = null;
$delimiter     = null;
$startFileName = null;

$files = $client->listFileNames($bucketId, $prefix, $delimiter, $startFileName);

foreach ($files as $file) {
	echo($file->name());
}
```

## Copying Files

See [`b2_copy_file`](https://www.backblaze.com/b2/docs/b2_copy_file.html).

To copy a file in the same bucket, simply:

```php
$file = $client->copyFile($sourceFileId, $fileName);

echo($file->id());  // The file ID of the copied file
```

For a more complex copy operation:

```php
$file = $client->copyFile($sourceFileId, $fileName, $destinationBucketId, $range, $metadataDirective, $contentType, $fileInfo);
```

## Hiding Files

See [`b2_hide_file`](https://www.backblaze.com/b2/docs/b2_hide_file.html).

```php
$file = $client->hideFile($bucketId, $fileName);

echo($file->getAction()); // The action will now be "hide"
```

## Getting File Information

See [`b2_get_file_info`](https://www.backblaze.com/b2/docs/b2_get_file_info.html).

```php
$file = $client->getFileInfo($fileId);
```

## Listing File Versions

See [`b2_list_file_versions`](https://www.backblaze.com/b2/docs/b2_list_file_versions.html).

```php
$files = $client->listFileVersions($bucketId);
```

## The `File` Object

This library will create instances of `Zaxbux\BackBlazeB2\File` that contain information about a file.

Properties:
 * `id` - The file ID.
 * `name` - The file name.
 * `checksum` - The SHA1 checksum.
 * `size` - The size of the file (bytes).
 * `type` - The file's Content-Type.
 * `info` - Custom info about the file.
 * `bucketId` - The bucket ID that the file belongs to.
 * `accountId` - The account ID that the file belongs to.
 * `action` - One of *start*, *upload*, *hide*, or *folder*. See [`b2_list_file_versions`](https://www.backblaze.com/b2/docs/b2_list_file_versions.html).
 * `uploadTimestamp` - A UTC timestamp when the file was uploaded.

Methods:
 * `id()`
 * `setId()`
 * `name()`
 * `setname()`
 * `getChecksum()`
 * `setChecksum()`
 * `getSize()`
 * `setSize()`
 * `getType()`
 * `setType()`
 * `getInfo()`
 * `setInfo()`
 * `bucketId()`
 * `setBucketId()`
 * `accountId()`
 * `setAccountId()`
 * `getAction()`
 * `setAction()`
 * `uploadTimestamp()`
 * `setUploadTimestamp()`
 * `isUpload()` - Returns **true** if the object is a file.
 * `isUnfinishedLargeFile()` - Returns **true** if the object is a large file upload that is not finished.
 * `isHidden()` - Returns **true** if the object is a file version that has been hidden.
 * `isFolder()` - Returns **true** if the object is a virtual folder.