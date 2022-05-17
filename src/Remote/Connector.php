<?php

namespace Ordnael\Configuration\Remote;

use Interfaces\ConnectorInterface;
use PDO;
use Exception;
use Throwable;

/**
 * Connector class for establishing database connections.
 */
class Connector implements ConnectorInterface
{
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
	 * Stores database username information.
	 * From environment variable:
	 * 
	 *   RA_CONFIG_DB_USERNAME='username'
	 * 
	 * @var string
	 */
	private $username;

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

	/**
	 * Stores database type information.
	 * From environment variable:
	 * 
	 *   RA_CONFIG_DB_TYPE='mysql|postgresql|sqlite3|mongodb|redis|memcache|memcached'
	 * 
	 * @var string
	 */
	protected $type;

	/**
	 * Stores database name information.
	 * From environment variable:
	 * 
	 *   RA_CONFIG_DB_DATABASE='database'
	 * 
	 * @var string
	 */
	protected $database;

	/**
	 * Default PDO connection options.
	 * 
	 * @var array<string, mixed>
	 */
	protected $options = [
		PDO::ATTR_CASE              => PDO::CASE_NATURAL,
		PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_ORACLE_NULLS      => PDO::NULL_NATURAL,
		PDO::ATTR_STRINGIFY_FETCHES => false,
		PDO::ATTR_EMULATE_PREPARES  => false
	];

	/**
	 * Stores the PDO connection.
	 * 
	 * @var \PDO
	 */
	private static $connection;

	/**
	 * Connector constructor.
	 * 
	 * @return $this
	 */
	private function __construct()
	{
		// if (cache_decrypted_values()) {
		//  openssl_private_decrypt($this->password, $password, $this->key);
		//  $this->password = $password;
		// }

		// self::$connection = $this->connect();
	}

	/**
	 * Handle connection exceptions.
	 * 
	 * @param  \Throwable  $e
	 * @param  string      $dsn
	 * @return \PDO
	 * 
	 * @throws \Exception
	 */
	private function retryIfLostConnection(Throwable $e, string $dsn)
	{
		if ($this->lostConnection($e)) {
			return new PDO($dsn, $this->username, $this->password, $this->options);
		}

		throw $e;
	}

	/**
	 * Determine if exception was caused because of lost connection.
	 * 
	 * @param  \Throwable  $e
	 * @return bool
	 */
	private function lostConnection(Throwable $e)
	{
		$message = $e->getMessage();
		$lost_connection_indicators = [
			'server has gone away',
			'no connection to the server',
			'Lost connection',
			'is dead or not enabled',
			'Error while sending',
			'decryption failed or bad record mac',
			'server closed the connection unexpectedly',
			'SSL connection has been closed unexpectedly',
			'Error writing data to the connection',
			'Resource deadlock avoided',
			'Transaction() on null',
			'child connection forced to terminate due to client_idle_limit',
			'query_wait_timeout',
			'reset by peer',
			'Physical connection is not usable',
			'TCP Provider: Error code 0x68',
			'ORA-03114',
			'Packets out of order. Expected',
			'Adaptive Server connection failed',
			'Communication link failure',
			'connection is no longer usable',
			'Login timeout expired',
			'SQLSTATE[HY000] [2002] Connection refused',
			'running with the --read-only option so it cannot execute this statement',
			'The connection is broken and recovery is not possible. The connection is marked by the client driver as unrecoverable. No attempt was made to restore the connection.',
			'SQLSTATE[HY000] [2002] php_network_getaddresses: getaddrinfo failed: Try again',
			'SQLSTATE[HY000] [2002] php_network_getaddresses: getaddrinfo failed: Name or service not known',
			'SQLSTATE[HY000]: General error: 7 SSL SYSCALL error: EOF detected',
			'SQLSTATE[HY000] [2002] Connection timed out',
			'SSL: Connection timed out',
			'SQLSTATE[HY000]: General error: 1105 The last transaction was aborted due to Seamless Scaling. Please retry.',
			'Temporary failure in name resolution',
			'SSL: Broken pipe',
			'SQLSTATE[08S01]: Communication link failure',
			'SQLSTATE[08006] [7] could not connect to server: Connection refused Is the server running on host',
			'SQLSTATE[HY000]: General error: 7 SSL SYSCALL error: No route to host',
			'The client was disconnected by the server because of inactivity. See wait_timeout and interactive_timeout for configuring this behavior.',
			'SQLSTATE[08006] [7] could not translate host name',
			'TCP Provider: Error code 0x274C',
			'SQLSTATE[HY000] [2002] No such file or directory',
		];

		foreach ($lost_connection_indicators as $lost_connection) {
			if (strpos($message, $lost_connection) !== false) return true;
		}

		return false;
	}

	/**
	 * Connect to the database.
	 * 
	 * @return \PDO
	 */
	protected function connect()
	{
		$dsn = "{$this->type}:host={$this->host};port={$this->port};dbname={$this->database}";
		
		try {
			return new PDO($dsn, $this->username, $this->password, $this->options);
		} catch (Exception $e) {
			return $this->retryIfLostConnection($e, $dsn);
		}
	}

	/**
	 * Get the database connection.
	 * 
	 * @return \PDO
	 */
	public static function getConnection()
	{
		if (! isset(self::$connection)) {
			$conn = new Connector();
		}

		return self::$connection;
	}

	/**
	 * Close database connection.
	 * 
	 * @return void
	 */
	public static function close()
	{
		self::$connection = null;
	}
}
