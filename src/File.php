<?php

namespace Zaxbux\BackblazeB2;

class File {
	const ACTION_START  = 'start';
	const ACTION_UPLOAD = 'upload';
	const ACTION_HIDE   = 'hide';
	const ACTION_FOLDER = 'folder';

	protected $id;
	protected $name;
	protected $sha1;
	protected $size;
	protected $type;
	protected $info;
	protected $bucketId;
	protected $accountId;
	protected $action;
	protected $uploadTimestamp;

	/**
	 * File constructor.
	 *
	 * @param $value       array The data to hydrate a new instance with
	 * @param $APIResponse bool  Hydration data is from the API
	 */
	public function __construct($value = [], $APIResponse = false) {
		if (empty($value)) {
			return;
		}

		if ($APIResponse) {
			$this->hydrateFromAPI($value);
		} else {
			$this->hydrate($value);
		}
	}

	/**
	 * Get the file ID
	 */ 
	public function getId() {
		return $this->id;
	}

	/**
	 * Set the file ID
	 *
	 * @return  self
	 */ 
	public function setId($id) {
		$this->id = $id;

		return $this;
	}

	/**
	 * Get the file name
	 */ 
	public function getName() {
		return $this->name;
	}

	/**
	 * Set the file name
	 *
	 * @return  self
	 */ 
	public function setName($name) {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get the file sha1
	 */ 
	public function getSha1() {
		return $this->sha1;
	}

	/**
	 * Set the file sha1
	 *
	 * @return  self
	 */ 
	public function setSha1($sha1) {
		$this->sha1 = $sha1;

		return $this;
	}

	/**
	 * Get the file size
	 */ 
	public function getSize() {
		return $this->size;
	}

	/**
	 * Set the file size
	 *
	 * @return  self
	 */ 
	public function setSize($size) {
		$this->size = $size;

		return $this;
	}

	/**
	 * Get the file type
	 */ 
	public function getType() {
		return $this->type;
	}

	/**
	 * Set the file type
	 *
	 * @return  self
	 */ 
	public function setType($type) {
		$this->type = $type;

		return $this;
	}

	/**
	 * Get the file info
	 */ 
	public function getInfo() {
		return $this->info;
	}

	/**
	 * Set the file info
	 *
	 * @return  self
	 */ 
	public function setInfo($info) {
		$this->info = $info;

		return $this;
	}

	/**
	 * Get the file bucket ID
	 */ 
	public function getBucketId() {
		return $this->bucketId;
	}

	/**
	 * Set the file bucket ID
	 *
	 * @return  self
	 */ 
	public function setBucketId($bucketId) {
		$this->bucketId = $bucketId;

		return $this;
	}

	/**
	 * Get the file account ID
	 */ 
	public function getAccountId() {
		return $this->accountId;
	}

	/**
	 * Set the file account ID
	 *
	 * @return  self
	 */ 
	public function setAccountId($accountId) {
		$this->accountId = $accountId;

		return $this;
	}

	/**
	 * Get the file action.
	 * "upload" means this file is uploaded to B2.
	 * "start" means this is an unfinished large file upload.
	 * "hide" means this file is a previous version, thus hidden.
	 * "folder" means this is a virtual folder.
	 */ 
	public function getAction() {
		return $this->action;
	}

	/**
	 * Set the file action
	 *
	 * @return  self
	 */ 
	public function setAction($action) {
		$this->action = $action;

		return $this;
	}

	/**
	 * Get the file upload timestamp
	 */ 
	public function getUploadTimestamp() {
		return $this->uploadTimestamp;
	}

	/**
	 * Set the file upload timestamp
	 *
	 * @return  self
	 */ 
	public function setUploadTimestamp($uploadTimestamp) {
		$this->uploadTimestamp = $uploadTimestamp;

		return $this;
	}

	/**
	 * Check if this file object is a finished upload.
	 * 
	 * @return bool
	 */
	public function isUpload() {
		return $this->action === self::ACTION_UPLOAD;
	}

	/**
	 * Check if this file object is an unfinished large file.
	 * 
	 * @return bool
	 */
	public function isUnfinishedLargeFile() {
		return $this->action === self::ACTION_START;
	}

	/**
	 * Check if this file object is a previous file version, thus hidden.
	 * 
	 * @return bool
	 */
	public function isHidden() {
		return $this->action === self::ACTION_HIDE;
	}

	/**
	 * Check if this file object is a virtual folder.
	 * 
	 * @return bool
	 */
	public function isFolder() {
		return $this->action === self::ACTION_FOLDER;
	}

	protected function hydrate($value) {
		foreach ($data as $attribute => $value) {
			$method = 'set'.str_replace('_', '', ucwords($attribute, '_'));
			if (is_callable([$this, $method])) {
				$this->$method($value);
			}
		}
	}

	protected function hydrateFromAPI($value) {
		$apiResponseFields = [
			'contentLength' => 'size',
			'contentSha1'   => 'sha1',
			'contentType'   => 'type',
			'fileId'        => 'id',
			'fileInfo'      => 'info',
			'fileName'      => 'name',
		];

		foreach ($data as $attribute => $value) {
			// Convert API response fields to class attribute names
			if (array_key_exists($attribute, $apiResponseFields)) {
				$attribute = $apiResponseFields[$attribute];
			}

			$method = 'set'.ucwords($attribute);
			if (is_callable([$this, $method])) {
				$this->$method($value);
			}
		}
	}
}
