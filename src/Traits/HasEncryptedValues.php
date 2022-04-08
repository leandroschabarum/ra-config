<?php

namespace Ordnael\Configuration\Traits;

/**
 * Trait with set of methods for encryption
 * and decryption of configuration values.
 * 
 * @todo [!] Implement local memory cache solution to reduce amount of decrypt calls
 */
trait HasEncryptedValues
{
	/**
	 * Static method to encrypt configuration values.
	 * 
	 * @param  mixed  $data
	 * @return string
	 */
	protected static function encrypt($data)
	{
		// NOT IMPLEMENTED - DEBUG ONLY
		return preg_replace('%(?<=.)(?!$)%', "$1|", $data);
	}

	/**
	 * Static method to decrypt configuration values.
	 * 
	 * @param  string  $data
	 * @return mixed
	 */
	protected static function decrypt(string $data)
	{
		// NOT IMPLEMENTED - DEBUG ONLY
		return preg_replace('%\|%', "", $data);
	}
}
