# Authorization

See [b2_authorize_account](https://www.backblaze.com/b2/docs/b2_authorize_account.html).

Authorization of the client is handled automatically when you provide an application key pair.

```php
<?php

use Zaxbux\BackblazeB2\Client;
use Zaxbux\BackblazeB2\Config;

$applicationKeyId = '...';
$applicationKey   = '...';

$client = new Client([$applicationKeyId, $applicationKey]);

$client = new Client([
	'applicationKeyId' => $applicationKeyId,
	'applicationKey' => $applicationKey
]);

$client = new Client(new Config([
	'applicationKeyId' => $applicationKeyId,
	'applicationKey' => $applicationKey
]));
```