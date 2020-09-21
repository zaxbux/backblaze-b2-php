# Buckets

## Listing Buckets

See [b2_list_buckets](https://www.backblaze.com/b2/docs/b2_list_buckets.html).

```php
$buckets = $client->listBuckets();
```

## Creating a Bucket

See [b2_create_bucket](https://www.backblaze.com/b2/docs/b2_create_bucket.html).

```php
$bucketName = '...';

$bucket = $client->createBucket($bucketName);
```

> **Warning**
> 
> By default, `createBucket()` will create a private bucket, which means authorization will be required to download files. Public buckets can also be created, however the files in these buckets can be downloaded by anyone (without authorization).

## Updating a Bucket

See [b2_update_bucket](https://www.backblaze.com/b2/docs/b2_update_bucket.html).

```php
$bucketId = '..';
$newBucketType = Bucket::TYPE_PUBLIC;

$bucket = $client->updateBucket($bucketId, $newBucketType);

```

## Deleting a Bucket

See [b2_delete_bucket](https://www.backblaze.com/b2/docs/b2_delete_bucket.html).

```php
$bucketId = '..';

$bucket = $client->deleteBucket($bucketId);
```