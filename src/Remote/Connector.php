<?php

namespace Ordnael\Configuration\Remote;

use Ordnael\Configuration\Remote\Interfaces\ConnectorInterface;
use Ordnael\Configuration\Remote\Traits\DetectsLostConnection;
use Ordnael\Configuration\Remote\Traits\PasswordAtRuntime;
use Ordnael\Configuration\Remote\Traits\HasConnectionOptions;
use Exception;
use Throwable;
use PDO;

/**
 * Connector class for establishing database connections.
 */
class Connector implements ConnectorInterface
{
	use DetectsLostConnection, PasswordAtRuntime, HasConnectionOptions;

	/**
	 * Stores the Connector instance.
	 * 
	 * @var \Ordnael\Configuration\Remote\Connector
	 */
	private static $connector;

	/**
	 * Stores the PDO connection.
	 * 
	 * @var \PDO
	 */
	private $connection;

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
	 * Stores database name information.
	 * From environment variable:
	 * 
	 *   RA_CONFIG_DB_DATABASE='database'
	 * 
	 * @var string
	 */
	protected $database;

	/**
	 * Stores database driver information.
	 * From environment variable:
	 * 
	 *   RA_CONFIG_DB_DRIVER='mysql|pgsql|sqlite|sqlsrv|mongodb|redis|memcache|memcached'
	 * 
	 * @var string
	 */
	protected $driver;

	/**
	 * Stores database host information.
	 * From environment variable:
	 * 
	 *   RA_CONFIG_DB_HOST='#.#.#.#'
	 * 
	 * @var string
	 */
	protected $host;

	/**
	 * Stores database port information.
	 * From environment variable:
	 * 
	 *   RA_CONFIG_DB_PORT='****'
	 * 
	 * @var int
	 */
	protected $port;

	/**
	 * Connector constructor.
	 * 
	 * @return $this
	 */
	private function __construct()
	{
		// Object properties setup from environment variables
		$this->username = getenv('RA_CONFIG_DB_USERNAME', true) ?: null;
		$this->database = getenv('RA_CONFIG_DB_DATABASE', true) ?: null;
		$this->driver = getenv('RA_CONFIG_DB_DRIVER', true) ?: null;
		$this->host = getenv('RA_CONFIG_DB_HOST', true) ?: null;
		$this->port = getenv('RA_CONFIG_DB_PORT', true) ?: null;

		$this->connection = $this->connect();
	}

	/**
	 * Get the database connector instance.
	 * 
	 * @return \Ordnael\Configuration\Remote\Connector
	 */
	public static function getConnector()
	{
		if (! isset(self::$connector) || ! self::$connector instanceof Connector) {
			self::$connector = new Connector();
		}

		return self::$connector;
	}

	/**
	 * Connect to the database.
	 * 
	 * @return \PDO
	 * 
	 * @throws \Exception
	 */
	public function connect()
	{
		if (isset($this->connection) && $this->connection instanceof PDO) {
			$this->close();
			throw new Exception("Attempt to connect on already established connection");
		};
		
		$dsn = "{$this->driver}:host={$this->host};port={$this->port};dbname={$this->database}";
		
		try {
			return new PDO($dsn, $this->username, $this->password(), $this->getOptions());
		} catch (Exception $e) {
			return $this->retryIfLostConnection($e, $dsn);
		}
	}

	/**
	 * Close database connection.
	 * 
	 * @return void
	 */
	public function close()
	{
		$this->connection = null;
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
		if ($this->isLostConnection($e)) {
			return new PDO($dsn, $this->username, $this->password(), $this->getOptions());
		}

		throw $e;
	}
}
