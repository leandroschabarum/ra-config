<?php

namespace Ordnael\Configuration\Remote;

use Ordnael\Configuration\Remote\Interfaces\ConnectorInterface;
use Ordnael\Configuration\Remote\Traits\DetectsLostConnection;
use Ordnael\Configuration\Remote\Traits\HasPasswordAtRuntime;
use Ordnael\Configuration\Remote\Traits\HasConnectionOptions;
use Ordnael\Configuration\Exceptions\ConnectOnOpenConnectionException;
use Ordnael\Configuration\Exceptions\UnavailableConnectionException;
use Exception;
use Throwable;
use PDO;

/**
 * Connector class for establishing database connections.
 */
class Connector implements ConnectorInterface
{
	use DetectsLostConnection, HasPasswordAtRuntime, HasConnectionOptions;

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
	final public static function getConnector()
	{
		if (! isset(self::$connector) || ! self::$connector instanceof Connector) {
			self::$connector = new Connector();
		}

		return self::$connector;
	}

	/**
	 * Handle connection exceptions.
	 * 
	 * @param  \Throwable  $e
	 * @param  string      $dsn
	 * @return \PDO
	 * 
	 * @throws \PDOException
	 */
	private function retryIfLostConnection(Throwable $e, string $dsn)
	{
		// Guard to resolve exceptions that are not related to lost connections
		if (! $this->isLostConnection($e)) throw $e;

		if (in_array($this->driver, PDO::getAvailableDrivers())) {
			// Case when connection was lost and database driver is available to PDO
			return new PDO($dsn, $this->username, $this->password(), $this->getOptions($this->driver));
		}
	}

	/**
	 * Access underlying connection object.
	 * 
	 * @return \PDO
	 * 
	 * @throws \Exception
	 */
	protected function connection()
	{
		// Guard to resolve undefined connections
		if (! isset($this->connection)) throw new UnavailableConnectionException();
		
		return $this->connection;
	}

	/**
	 * Connect to the database.
	 * 
	 * @return \PDO
	 * 
	 * @throws \Ordnael\Configuration\Exceptions\ConnectOnOpenConnectionException
	 */
	final public function connect()
	{
		if (in_array($this->driver, PDO::getAvailableDrivers())) {
			// Case when database driver is available to PDO
			if (isset($this->connection) && $this->connection instanceof PDO) {
				$this->close();
				throw new ConnectOnOpenConnectionException();
			};
			
			$dsn = "{$this->driver}:host={$this->host};port={$this->port};dbname={$this->database}";
			
			try {
				return new PDO($dsn, $this->username, $this->password(), $this->getOptions($this->driver));
			} catch (Exception $e) {
				return $this->retryIfLostConnection($e, $dsn);
			}
		}
	}

	/**
	 * Close database connection.
	 * 
	 * @return void
	 */
	final public function close()
	{
		// Guard to resolve undefined connections
		if (! isset($this->connection)) return null;

		if ($this->connection instanceof PDO) {
			// Close underlying connection established with PDO
			$this->connection = null;
		}
	}

	/**
	 * Retrieve database server version.
	 * 
	 * @return string|null
	 */
	public function version()
	{
		// Guard to resolve undefined connections
		if (! isset($this->connection)) return null;

		if ($this->connection instanceof PDO) {
			// Get server version from underlying PDO connection
			return $this->connection->getAttribute(PDO::ATTR_SERVER_VERSION);
		}
	}
}
