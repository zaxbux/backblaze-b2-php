<?php

namespace Zaxbux\BackblazeB2\B2Object;

/**
 * A Lifecycle rule object for the Backblaze B2 API
 */
class LifecycleRule implements \JsonSerializable {

	/**
	 * @var string The files in a bucket that this rule applies to.
	 */
	protected $fileNamePrefix = '';

	/**
	 * @var string How long to wait before hiding file versions that are the current version.
	 */
	protected $daysFromUploadingToHiding = null;

	/**
	 * @var string How long to keep file versions that are not the current version.
	 */
	protected $daysFromHidingToDeleting = null;

	/**
	 * Get the files in a bucket that this rule applies to.
	 *
	 * @return string
	 */ 
	public function getFileNamePrefix() {
		return $this->fileNamePrefix;
	}

	/**
	 * Set the files in a bucket that this rule applies to.
	 *
	 * @param  string  $fileNamePrefix  The files in a bucket that this rule applies to.
	 *
	 * @return self
	 */ 
	public function setFileNamePrefix(string $fileNamePrefix) {
		$this->fileNamePrefix = $fileNamePrefix;

		return $this;
	}

	/**
	 * Get how long to wait before hiding file versions that are the current version.
	 *
	 * @return string
	 */ 
	public function getDaysFromUploadingToHiding() {
		return $this->daysFromUploadingToHiding;
	}

	/**
	 * Set how long to wait before hiding file versions that are the current version.
	 *
	 * @param  string  $daysFromUploadingToHiding  How long to wait before hiding file versions that are the current version.
	 *
	 * @return self
	 */ 
	public function setDaysFromUploadingToHiding(string $daysFromUploadingToHiding) {
		$this->daysFromUploadingToHiding = $daysFromUploadingToHiding;

		return $this;
	}

	/**
	 * Get how long to keep file versions that are not the current version.
	 *
	 * @return string
	 */ 
	public function getDaysFromHidingToDeleting() {
		return $this->daysFromHidingToDeleting;
	}

	/**
	 * Set how long to keep file versions that are not the current version.
	 *
	 * @param  string  $daysFromHidingToDeleting  How long to keep file versions that are not the current version.
	 *
	 * @return self
	 */ 
	public function setDaysFromHidingToDeleting(string $daysFromHidingToDeleting) {
		$this->daysFromHidingToDeleting = $daysFromHidingToDeleting;

		return $this;
	}

	public function jsonSerialize() {
		return [
			'fileNamePrefix'            => $this->fileNamePrefix,
			'daysFromUploadingToHiding' => $this->daysFromUploadingToHiding,
			'daysFromHidingToDeleting'  => $this->daysFromHidingToDeleting,
		];
	}
}