<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Helpers;

use BadMethodCallException;
use Zaxbux\BackblazeB2\Object\Key;
use Zaxbux\BackblazeB2\Response\KeyList;

/** @package BackblazeB2\Helpers */
class ApplicationKeyOperationsHelper extends AbstractHelper {

	/** @var \Zaxbux\BackblazeB2\Object\Key */
	private $applicationKey;

	/**
	 * Specify which applicationKey to preform operations on. Must call this method before calling `delete()`.
	 * @param null|string|Key $applicationKey 
	 * @return ApplicationKeyOperationsHelper 
	 */
	public function withApplicationKey($applicationKey = null): ApplicationKeyOperationsHelper
	{
		if ($applicationKey instanceof Key) {
			$this->applicationKey = $applicationKey;
		}

		// Only the applicationKeyId is required for helper methods
		if (is_string($applicationKey)) {
			$this->applicationKey = new Key($applicationKey);
		}
		
		return $this;
	}

	public function create(
		string $keyName,
		array $capabilities,
		?int $validDuration = null,
		?string $bucketId = null,
		?string $namePrefix = null
	): Key {
		return $this->client->createKey($keyName, $capabilities, $validDuration, $bucketId, $namePrefix);
	}

	public function list(
		?string $startApplicationKeyId = null,
		?int $maxKeyCount = null
	): KeyList
	{
		return $this->client->listKeys($startApplicationKeyId, $maxKeyCount);
	}

	public function listAll(?string $startApplicationKeyId = null): KeyList {
		return $this->client->listAllKeys($startApplicationKeyId);
	}

	public function delete(): Key
	{
		$this->assertApplicationKeyIsSet();
		$this->applicationKey = $this->client->deleteKey($this->applicationKey->applicationKeyId());
		return $this->applicationKey;
	}

	private function assertApplicationKeyIsSet(): void
	{
		if (!$this->applicationKey) {
			throw new BadMethodCallException('$applicationKey is not set');
		}
	}
}