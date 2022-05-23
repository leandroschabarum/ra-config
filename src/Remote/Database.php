<?php

namespace Ordnael\Configuration\Remote;

use Ordnael\Configuration\Remote\Interfaces\CrudInterface;
use Ordnael\Configuration\Remote\Traits\HasQueries;
use Ordnael\Configuration\Remote\Traits\HasQueryHistory;
use Ordnael\Configuration\Remote\Traits\HasMultiGrammar;
use Ordnael\Configuration\Exceptions\InvalidSchemaKeyException;
use Ordnael\Configuration\Schema;
use Exception;
use PDO;

/**
 * Database class for executing configuration operations.
 */
class Database extends Connector implements CrudInterface
{
	use HasQueries;
	use HasQueryHistory;
	use HasMultiGrammar;

	/**
	 * Default name for the configuration database table.
	 */
	const TABLE = 'config';

	/**
	 * Database constructor.
	 * 
	 * @return $this
	 */
	public function __construct()
	{
		$this->type = self::getConnector()->driver;
		$this->table = self::from(true);
	}

	/**
	 * Migration method for database setup.
	 * 
	 * @param  bool  $fresh
	 * @return bool
	 * 
	 * @throws \Exception
	 */
	public function migrate(bool $fresh = false)
	{
		$statement = self::createTableStatement($this->type, $this->table, $fresh);

		if ($statement) return self::getConnector()->connection()
			->exec(self::keepHistory($statement)) !== false;

		throw new Exception("Unable to process migration statement for {$this->type}.");
	}

	/**
	 * Convert mixed values to string for database storage.
	 * 
	 * @param  mixed  $value
	 * @return string
	 * 
	 * @throws \Exception
	 */
	protected function toString($value)
	{
		$type = gettype($value);

		switch ($type) {
			case 'string':
			case 'integer':
			case 'double':
				break;

			case 'NULL':
				$value = 'NULL';
				break;

			case 'boolean':
				$value = $value ? '1' : '0';
				break;

			case 'array':
				$value = json_encode($value);
				break;
			
			default:
				throw new Exception("Unable to convert {$type} to string.");
		}

		return $value;
	}

	/**
	 * Read configuration key.
	 * 
	 * @param  string  $key
	 * @return \Ordnael\Configuration\Schema|null
	 */
	public function select(string $key)
	{
		if (! Schema::isValidKey($key)) throw new InvalidSchemaKeyException($key);

		$statement = $this->createSelectStatement($this->table, $key);

		$result = self::getConnector()->connection()
		->query(self::keepHistory($statement))->fetch(PDO::FETCH_NUM);

		// Transform to Schema object
		return ! empty($result) ? Schema::create(...$result) : null;
	}

	/**
	 * Create configuration key/value pair.
	 * 
	 * @param  string  $key
	 * @return int|bool
	 */
	public function insert(string $key, $value)
	{
		if (! Schema::isValidKey($key)) throw new InvalidSchemaKeyException($key);

		$statement = $this->createInsertStatement($this->table, $key, $this->toString($value));

		if (self::getConnector()->connection()->exec(self::keepHistory($statement)) !== false) {
			return (int) self::getConnector()->connection()->lastInsertId('id');
		}

		return false;
	}

	/**
	 * Update value from configuration key.
	 * 
	 * @param  string  $key
	 * @return bool
	 */
	public function update(string $key, $value)
	{
		if (! Schema::isValidKey($key)) throw new InvalidSchemaKeyException($key);

		$statement = $this->createUpdateStatement($this->table, $key, $this->toString($value));

		return self::getConnector()->connection()
		->exec(self::keepHistory($statement)) !== false;
	}

	/**
	 * Delete configuration key/value pair.
	 * 
	 * @param  string  $key
	 * @return bool
	 */
	public function remove(string $key)
	{
		if (! Schema::isValidKey($key)) throw new InvalidSchemaKeyException($key);

		$statement = $this->createDeleteStatement($this->table, $key);

		return self::getConnector()->connection()
		->exec(self::keepHistory($statement)) !== false;
	}
}
