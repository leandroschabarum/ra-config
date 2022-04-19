<?php

namespace Ordnael\Configuration\Remote;

/**
 * Connector class for establishing database connections.
 */
class Connector
{
	/**
	 * Stores database type information.
	 * From environment variable:
	 * 
	 *   RA_CONFIG_DB_TYPE='mysql|postgresql|sqlite3|mongodb|redis|memcache|memcached'
	 * 
	 * @var string
	 */
	private $type;

	/**
	 * Stores database host information.
	 * From environment variable:
	 * 
	 *   RA_CONFIG_DB_HOST='#.#.#.#'
	 * 
	 * @var string
	 */
	private $host;

	/**
	 * Stores database port information.
	 * From environment variable:
	 * 
	 *   RA_CONFIG_DB_PORT='****'
	 * 
	 * @var int
	 */
	private $port;

	/**
	 * Stores database name information.
	 * From environment variable:
	 * 
	 *   RA_CONFIG_DB_DATABASE='database'
	 * 
	 * @var string
	 */
	private $database;

	/**
	 * Stores database username information.
	 * From environment variable:
	 * 
	 *   RA_CONFIG_DB_USERNAME='username'
	 * 
	 * @var string
	 */
	private $username; // RA_CONFIG_DB_USERNAME='username'

	/**
	 * Stores database password information.
	 * From environment variable:
	 * 
	 *   RA_CONFIG_DB_PASSWORD='password'
	 * 
	 * @var string
	 */
	private $password;

	/**
	 * Stores if database password is encrypted or not
	 * and where to find the private key for decryption.
	 * From environment variable:
	 * 
	 *   RA_CONFIG_DB_ENCRYPTED='true:privkey|false'
	 * 
	 * @var string
	 */
	private $key;

	private static $connection;

	/**
	 * Connector constructor.
	 */
	private function __construct()
	{
		if (cache_decrypted_values()) {
			openssl_private_decrypt($this->password, $password, $this->key);
			$this->password = $password;
		}
	}

	public static function connect() {
		if (! isset(self::$connection)) {
			self::$connection = new Connector();
		}

		return self::$connection;
	}
}
