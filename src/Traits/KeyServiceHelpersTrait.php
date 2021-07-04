<?php

declare(strict_types=1);

namespace Zaxbux\BackblazeB2\Traits;

use AppendIterator;
use NoRewindIterator;
use Zaxbux\BackblazeB2\B2\Response\KeyListResponse;

trait ApplicationKeyServiceHelpersTrait
{
	public abstract function listKeys(
		?string $startApplicationKeyId = null,
		?int $maxKeyCount = 1000
	): KeyListResponse;

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
