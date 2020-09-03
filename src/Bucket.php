<?php

namespace Zaxbux\BackblazeB2;

class Bucket {
	const TYPE_PUBLIC  = 'allPublic';
	const TYPE_PRIVATE = 'allPrivate';

	protected $id;
	protected $name;
	protected $type;

	/**
	 * Bucket constructor.
	 *
	 * @param $id
	 * @param $name
	 * @param $type
	 */
	public function __construct($id, $name, $type) {
		$this->id   = $id;
		$this->name = $name;
		$this->type = $type;
	}

	/**
	 * Get bucket ID
	 * 
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Get bucket name
	 * 
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Get bucket type
	 */
	public function getType() {
		return $this->type;
	}
}
