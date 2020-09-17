# Authorization

See [b2_authorize_account](https://www.backblaze.com/b2/docs/b2_authorize_account.html).

Authorization of the client is handled automatically when you provide your account ID and application key.

```php
<?php

use Zaxbux\BackblazeB2\Client;

$accountId      = '...';
$applicationKey = '...';

$client = new Client($accountId, $applicationKey);
```

## Download Authorization

See [b2_get_download_authorization](https://www.backblaze.com/b2/docs/b2_get_download_authorization.html).

To generate an authorization token for a private bucket, use the `getDownloadAuthorization()` method.

```php
$authorization = $client->getDownloadAuthorization($bucketId, $fileNamePrefix, $validDuration, $options);
```