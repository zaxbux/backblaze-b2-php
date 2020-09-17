# Application Keys

## Create a Key

See [b2_create_key](https://www.backblaze.com/b2/docs/b2_create_key.html).

```php
$keyName = '...';
$capabilities = [];

$key = $client->createKey($keyName, $capabilities);
```

## Delete a Key

See [b2_delete_key](https://www.backblaze.com/b2/docs/b2_delete_key.html).

```php
$keyName = '...';

$key = $client->deletekey($keyName);
```