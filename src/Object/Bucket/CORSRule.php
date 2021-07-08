<?php

namespace Zaxbux\BackblazeB2\Object\Bucket;

use JsonSerializable;

/**
 * A CORS rule object.
 * 
 * @package BackblazeB2\Object\Bucket
 */
class CORSRule implements JsonSerializable {

	/**
	 * @var string A name for humans to recognize the rule in a user interface.
	 */
	protected $corsRulename;

	/**
	 * @var array A non-empty list specifying which origins the rule covers.
	 */
	protected $allowedOrigins;

	/**
	 * @var array A list specifying which operations the rule allows. At least one value must be specified.
	 */
	protected $allowedOperations;

	/**
	 * @var array A list of headers that are allowed in a pre-flight OPTIONS's request's Access-Control-Request-Headers header value.
	 */
	protected $allowedHeaders;

	/**
	 * @var array A list of headers that may be exposed to an application inside the client
	 */
	protected $exposeHeaders;

	/**
	 * @var int The maximum number of seconds that a browser may cache the response to a preflight request.
	 */
	protected $maxAgeSeconds;

	/**
	 * Get a name for humans to recognize the rule in a user interface.
	 *
	 * @return string
	 */ 
	public function getCorsRulename() {
		return $this->corsRulename;
	}

	/**
	 * Set a name for humans to recognize the rule in a user interface.
	 *
	 * @param  string  $corsRulename  A name for humans to recognize the rule in a user interface.
	 *
	 * @return self
	 */ 
	public function setCorsRulename(string $corsRulename) {
		$this->corsRulename = $corsRulename;

		return $this;
	}

	/**
	 * Get a non-empty list specifying which origins the rule covers.
	 *
	 * @return array
	 */ 
	public function getAllowedOrigins() {
		return $this->allowedOrigins;
	}

	/**
	 * Set a non-empty list specifying which origins the rule covers.
	 *
	 * @param  array  $allowedOrigins  A non-empty list specifying which origins the rule covers.
	 *
	 * @return self
	 */ 
	public function setAllowedOrigins(array $allowedOrigins) {
		$this->allowedOrigins = $allowedOrigins;

		return $this;
	}

	/**
	 * Get a list specifying which operations the rule allows. At least one value must be specified.
	 *
	 * @return array
	 */ 
	public function getAllowedOperations() {
		return $this->allowedOperations;
	}

	/**
	 * Set a list specifying which operations the rule allows. At least one value must be specified.
	 *
	 * @param  array  $allowedOperations  A list specifying which operations the rule allows. At least one value must be specified.
	 *
	 * @return self
	 */ 
	public function setAllowedOperations(array $allowedOperations) {
		$this->allowedOperations = $allowedOperations;

		return $this;
	}

	/**
	 * Get a list of headers that are allowed in a pre-flight OPTIONS's request's Access-Control-Request-Headers header value.
	 *
	 * @return array
	 */ 
	public function getAllowedHeaders() {
		return $this->allowedHeaders;
	}

	/**
	 * Set a list of headers that are allowed in a pre-flight OPTIONS's request's Access-Control-Request-Headers header value.
	 *
	 * @param  array  $allowedHeaders  A list of headers that are allowed in a pre-flight OPTIONS's request's Access-Control-Request-Headers header value.
	 *
	 * @return self
	 */ 
	public function setAllowedHeaders(array $allowedHeaders) {
		$this->allowedHeaders = $allowedHeaders;

		return $this;
	}

	/**
	 * Get a list of headers that may be exposed to an application inside the client
	 *
	 * @return array
	 */ 
	public function getExposeHeaders() {
		return $this->exposeHeaders;
	}

	/**
	 * Set a list of headers that may be exposed to an application inside the client
	 *
	 * @param  array  $exposeHeaders  A list of headers that may be exposed to an application inside the client
	 *
	 * @return self
	 */ 
	public function setExposeHeaders(array $exposeHeaders) {
		$this->exposeHeaders = $exposeHeaders;

		return $this;
	}

	/**
	 * Get the maximum number of seconds that a browser may cache the response to a preflight request.
	 *
	 * @return int
	 */ 
	public function getMaxAgeSeconds() {
		return $this->maxAgeSeconds;
	}

	/**
	 * Set the maximum number of seconds that a browser may cache the response to a preflight request.
	 *
	 * @param  int  $maxAgeSeconds  The maximum number of seconds that a browser may cache the response to a preflight request.
	 *
	 * @return self
	 */ 
	public function setMaxAgeSeconds(int $maxAgeSeconds) {
		$this->maxAgeSeconds = $maxAgeSeconds;

		return $this;
	}

	public function jsonSerialize() {
		$json = [
			'corsRulename'      => $this->corsRulename,
			'allowedOrigins'    => $this->allowedOrigins,
			'allowedOperations' => $this->allowedOperations,
			'maxAgeSeconds'     => $this->maxAgeSeconds,
		];

		if ($this->allowedHeaders) {
			$json['allowedHeaders'] = $this->allowedHeaders;
		}

		if ($this->exposeHeaders) {
			$json['exposeHeaders'] = $this->exposeHeaders;
		}

		return $json;
	}
}