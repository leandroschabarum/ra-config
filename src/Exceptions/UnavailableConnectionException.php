<?php

namespace Ordnael\Configuration\Exceptions;

use Exception;
use Throwable;

/**
 * Exception class for unavailable connection.
 */
class UnavailableConnectionException extends Exception
{
	/**
	 * Custom exception constructor.
	 * 
	 * @param  string      $text
	 * @param  int         $code
	 * @param  \Throwable  $previous
	 * @return $this
	 */
	public function __construct(string $text = null, int $code = 0, Throwable $previous = null)
	{
		$message = "Connection is not available, closed or undefined.";
		parent::__construct($message, $code, $previous);
	}

	/**
	 * Special method to represent class object as a string.
	 */
	public function __toString() {
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}
}

