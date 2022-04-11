<?php

namespace Ordnael\Configuration\Traits;

/**
 * Trait with set of methods for encryption
 * and decryption of configuration values.
 * 
 * OBS: set environment variable RA_CONFIG_KEY_FILE with secret key file path
 */
trait HasEncryptedValues
{
	/**
	 * Stores secret key for encrypting and
	 * decrypting configuration parameters.
	 * 
	 * @var string
	 */
	private static $secret_key;

	/**
	 * Method to setup encryption key on object.
	 * 
	 * @param  string  $key
	 * @return bool
	 */
	private function setupEncryptionKey($key = null)
	{
		$key_file = getenv('RA_CONFIG_KEY_FILE', true) ?: null;
		$key_size = SODIUM_CRYPTO_SECRETBOX_KEYBYTES ?? 32;

		if (is_string($key)) {
			// Case when base64 key string is a passed in parameter
			if (strlen($key) > $key_size) self::$secret_key = base64_decode($key);
			// Case when bytes key string is a passed in parameter
			if (strlen($key) == $key_size) self::$secret_key = $key;

			return strlen($key) >= $key_size ? true : false;
		} else if (file_exists($key_file)) {
			if (is_readable($key_file) && filesize($key_file) > $key_size) {
				// Case when base64 key string is stored in a file
				self::$secret_key = base64_decode(file_get_contents($key_file));

				return true;
			}

			return false;
		} else {
			// Case when no base64 key source is given
			$filename = $key_file ?? 'secret.key';
			self::$secret_key = random_bytes($key_size);

			file_put_contents($filename, base64_encode(self::$secret_key));

			return true;
		}

		return false;
	}

	/**
	 * Encryption method for configuration values.
	 * 
	 * @param  string  $data
	 * @return string
	 */
	protected static function encrypt(string $data)
	{
		$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES ?? 24);
		$cyphertext = sodium_crypto_secretbox($data, $nonce, self::$secret_key);

		return base64_encode($nonce) . ':' . base64_encode($cyphertext);
	}

	/**
	 * Decryption method for configuration values.
	 * 
	 * @param  string  $data
	 * @return mixed
	 */
	protected static function decrypt(string $data)
	{
		preg_match('%^(?<nonce>[\w+\/=]+):(?<cyphertext>[\w+\/=]+)$%', $data, $group);
		$nonce = base64_decode($group['nonce']);
		$cyphertext = base64_decode($group['cyphertext']);

		return sodium_crypto_secretbox_open($cyphertext, $nonce, self::$secret_key);
	}
}
