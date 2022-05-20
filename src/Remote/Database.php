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
		switch ($this->type) {
			case 'mysql':
			case 'mariadb':
				$statement = self::createMySqlTableStatement($this->table, $fresh);
				break;

			case 'pgsql':
				$statement = self::createPostgreSqlTableStatement($this->table, $fresh);
				break;

			case 'sqlsrv':
				$statement = self::createSqlServerTableStatement($this->table, $fresh);
				break;

			case 'sqlite':
				$statement = self::createSqliteTableStatement($this->table, $fresh);
				break;
			
			default:
				$statement = false;
		}

		if ($statement) return self::getConnector()->connection()
			->exec(self::keepHistory($statement)) !== false;

		throw new Exception("Unable to process migration statement for {$this->type}.");
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

		$statement = "SELECT `key`, `value`, `encrypted` FROM {$this->table} WHERE `key` = '{$key}';";

		$result = self::getConnector()->connection()
		->query(self::keepHistory($statement))->fetch(PDO::FETCH_NUM);

		// Convert to Schema object
		return ! empty($result) ? Schema::create(...$result) : null;
	}

	/**
	 * Create configuration key/value pair.
	 * 
	 * @param  string  $key
	 * @return \Ordnael\Configuration\Schema|null
	 */
	public function insert(string $key, $value)
	{
		if (! Schema::isValidKey($key)) throw new InvalidSchemaKeyException($key);

		switch (gettype($value)) {
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
				// code...
				break;
		}

		$statement = "INSERT INTO {$this->table} (`key`, `value`, `encrypted`) VALUES ('{$key}', '{$value}', '0');";

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
		//
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

		$statement = "DELETE FROM {$this->table} WHERE `key` = '{$key}';";

		return self::getConnector()->connection()
		->exec(self::keepHistory($statement)) !== false;
	}
}
