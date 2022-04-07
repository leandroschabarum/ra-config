<?php

namespace Ordnael\Configuration\Exceptions;

use Exception;
use Throwable;

/**
 * Exception class for not found schema fields.
 */
class SchemaFieldNotFoundException extends Exception
{
	/**
	 * Custom exception constructor.
	 * 
	 * @param  string     $field
	 * @param  int        $code
	 * @param  Throwable  $previous
	 * @return $this
	 */
	public function __construct(string $field, int $code = 0, Throwable $previous = null)
	{
		$message = "Field '{$field}' is missing or not allowed in the configuration schema.";
		parent::__construct($message, $code, $previous);
	}

	/**
	 * Special method to represent class object as a string.
	 */
	public function __toString() {
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}
}
