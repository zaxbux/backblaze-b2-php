<?php

namespace Zaxbux\BackblazeB2;

class Bucket {
	const TYPE_ALL      = 'all';
	const TYPE_PUBLIC   = 'allPublic';
	const TYPE_PRIVATE  = 'allPrivate';
	const TYPE_SNAPSHOT = 'snapshot';

	protected $id;
	protected $name;
	protected $type;
	protected $info;
	protected $options;
	protected $revision;
	protected $corsRules;
	protected $lifecycleRules;

	/**
	 * Bucket constructor.
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
	 * Get the value of id
	 */ 
	public function getId() {
		return $this->id;
	}

	/**
	 * Set the value of id
	 *
	 * @return  self
	 */ 
	public function setId($id) {
		$this->id = $id;

		return $this;
	}

	/**
	 * Get the value of name
	 */ 
	public function getName() {
		return $this->name;
	}

	/**
	 * Set the value of name
	 *
	 * @return  self
	 */ 
	public function setName($name) {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get the value of type
	 */ 
	public function getType() {
		return $this->type;
	}

	/**
	 * Set the value of type
	 *
	 * @return  self
	 */ 
	public function setType($type) {
		$this->type = $type;

		return $this;
	}

	/**
	 * Get the value of info
	 */ 
	public function getInfo() {
		return $this->info;
	}

	/**
	 * Set the value of info
	 *
	 * @return  self
	 */ 
	public function setInfo($info) {
		$this->info = $info;

		return $this;
	}

	/**
	 * Get the value of options
	 */ 
	public function getOptions() {
		return $this->options;
	}

	/**
	 * Set the value of options
	 *
	 * @return  self
	 */ 
	public function setOptions($options) {
		$this->options = $options;

		return $this;
	}

	/**
	 * Get the value of revision
	 */ 
	public function getRevision() {
		return $this->revision;
	}

	/**
	 * Set the value of revision
	 *
	 * @return  self
	 */ 
	public function setRevision($revision) {
		$this->revision = $revision;

		return $this;
	}

	/**
	 * Get the value of corsRules
	 */ 
	public function getCorsRules() {
		return $this->corsRules;
	}

	/**
	 * Set the value of corsRules
	 *
	 * @return  self
	 */ 
	public function setCorsRules($corsRules) {
		$this->corsRules = $corsRules;

		return $this;
	}

	/**
	 * Get the value of lifecycleRules
	 */ 
	public function getLifecycleRules() {
		return $this->lifecycleRules;
	}

	/**
	 * Set the value of lifecycleRules
	 *
	 * @return  self
	 */ 
	public function setLifecycleRules($lifecycleRules) {
		$this->lifecycleRules = $lifecycleRules;

		return $this;
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
			'bucketId'   => 'id',
			'bucketname' => 'name',
			'bucketType' => 'type',
			'bucketInfo' => 'info',
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
