<?php

namespace Zaxbux\BackblazeB2\Exceptions;

class B2APIException extends \Exception {

	protected $code;

	public function __construct($message, $code = null) {
		$this->code = $code;

		parent::__construct($message);
	}

	/**
	 * Get the error code
	 */ 
	public function getAPIErrorCode() {
		return $this->code;
	}
}