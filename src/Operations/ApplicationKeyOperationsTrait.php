<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Operations;

use AppendIterator;
use NoRewindIterator;
use Zaxbux\BackblazeB2\Object\AccountAuthorization;
use Zaxbux\BackblazeB2\Object\Key;
use Zaxbux\BackblazeB2\Response\KeyList;
use Zaxbux\BackblazeB2\Utils;

trait ApplicationKeyOperationsTrait
{

	/** @var \GuzzleHttp\ClientInterface */
	protected $http;

	abstract protected function accountAuthorization(): AccountAuthorization;

	/**
	 * Creates a new application key.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_create_key.html
	 * 
	 * @param string    $keyName       A name for this key. There is no requirement that the name be unique.
	 *                                 The name cannot be used to look up the key.
	 * @param string[]  $capabilities  A list of strings, each one naming a capability the new key should have.
	 * @param int       $validDuration The number of seconds before the key expires.
	 * @param string    $bucketId      Restrict access a specific bucket.
	 * @param string    $namePrefix    Restrict access to files whose names start with the prefix.
	 *                                 If specified, `$bucketId` must also be specified.
	 */
	public function createKey(
		string $keyName,
		array $capabilities,
		?int $validDuration = null,
		?string $bucketId = null,
		?string $namePrefix = null
	): Key {
		$response = $this->http->request('POST', '/b2_create_key', [
			'json' => Utils::filterRequestOptions([
				Key::ATTRIBUTE_ACCOUNT_ID    => $this->accountAuthorization()->getAccountId(),
				Key::ATTRIBUTE_CAPABILITIES  => $capabilities,
				Key::ATTRIBUTE_KEY_NAME      => $keyName,
			], [
				Key::ATTRIBUTE_BUCKET_ID      => $bucketId,
				Key::ATTRIBUTE_VALID_DURATION => $validDuration,
				Key::ATTRIBUTE_NAME_PREFIX    => $namePrefix,
			]),
		]);

		return Key::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Deletes the application key specified.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_delete_key.html
	 *
	 * @param string $applicationKeyId The key to delete.
	 */
	public function deleteKey(string $applicationKeyId): Key
	{
		$response = $this->http->request('POST', '/b2_delete_key', [
			'json' => [
				Key::ATTRIBUTE_APPLICATION_KEY_ID => $applicationKeyId,
			]
		]);

		return Key::fromArray(json_decode((string) $response->getBody(), true));
	}

	/**
	 * Lists application keys associated with an account.
	 * 
	 * @link https://www.backblaze.com/b2/docs/b2_list_keys.html
	 * 
	 * @param string $startApplicationKeyId The first key to return.
	 * @param int    $maxKeyCount           The maximum number of keys to return in the response. The default value is
	 *                                      1000, and the maximum is 10000. The maximum number of keys returned per
	 *                                      transaction is 1000.
	 */
	public function listKeys(
		?string $startApplicationKeyId = null,
		?int $maxKeyCount = 1000
	): KeyList {
		$response = $this->http->request('POST', '/b2_list_keys', [
			'json' => Utils::filterRequestOptions([
				Key::ATTRIBUTE_ACCOUNT_ID => $this->accountAuthorization()->getAccountId(),
			], [
				Key::ATTRIBUTE_MAX_KEY_COUNT => $maxKeyCount,
				Key::ATTRIBUTE_START_APPLICATION_KEY_ID => $startApplicationKeyId
			]),
		]);

		return KeyList::create($response);
	}

	/**
	 * Lists *all* application keys associated with an account.
	 * 
	 * @see Client::listKeys()
	 */
	public function listAllKeys(string $startApplicationKeyId = null, int $maxKeyCount = 1000, bool $loop = true): iterable
	{
		$allKeys = new AppendIterator();

		$nextApplicationKeyId = $startApplicationKeyId ?? '';

		while ($nextApplicationKeyId !== null) {
			$keys = $this->listKeys($startApplicationKeyId, $maxKeyCount);
			$nextApplicationKeyId = $keys->getNextApplicationKeyId();

			$allKeys->append(new NoRewindIterator($keys->getKeys()));
		}

		return $allKeys;
	}
}
