<?php

namespace Zaxbux\BackblazeB2\Traits;

use ArrayIterator;
use Zaxbux\BackblazeB2\Response\FileList;

trait DeleteAllFilesTrait {
	/**
	 * Deletes all versions of a file(s) in a bucket.
	 * 
	 * @see FileService::deleteFileVersion()
	 * 
	 * @param string      $bucketId         The ID of the bucket containing file versions to delete.
	 * @param null|string $prefix           
	 * @param null|string $delimiter        
	 * @param null|string $startFileName    
	 * @param null|string $startFileId      
	 * @param null|bool   $bypassGovernance 
	 */
	public function deleteAllFileVersions(
		string $bucketId,
		?string $prefix = '',
		?string $delimiter = null,
		?string $startFileName = null,
		?string $startFileId = null,
		?bool $bypassGovernance = false
	): FileList {
		$fileVersions = $this->listAllFileVersions($bucketId, $prefix, $delimiter, $startFileName, $startFileId);

		$deleted = [];

		while ($fileVersions->valid()) {
			$version = $fileVersions->current();

			$deleted[] = $this->deleteFileVersion($version->getName(), $version->getId(), $bypassGovernance);

			$fileVersions->next();
		}
		
		return new FileList(new ArrayIterator($deleted));
	}
}