<?php

namespace Ordnael\Configuration\Exceptions;

use Exception;
use Throwable;

/**
 * Exception class for invalid schema keys.
 */
class InvalidSchemaKeyException extends Exception
{
	/**
	 * Custom exception constructor.
	 * 
	 * @param  string     $key
	 * @param  int        $code
	 * @param  Throwable  $previous
	 * @return $this
	 */
	public function __construct(string $key, int $code = 0, Throwable $previous = null)
	{
		$message = "Key '{$key}' is not valid.";
		parent::__construct($message, $code, $previous);
	}

	/**
	 * Special method to represent class object as a string.
	 */
	public function __toString() {
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}
}
