<?php

namespace Ordnael\Configuration\Exceptions;

use Exception;
use Throwable;

/**
 * Exception class for not found schema fields.
 */
class ConnectOnOpenConnectionException extends Exception
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
		$message = "Attempt to connect on already established connection.";
		parent::__construct($message, $code, $previous);
	}

	/**
	 * Special method to represent class object as a string.
	 */
	public function __toString() {
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}
}

