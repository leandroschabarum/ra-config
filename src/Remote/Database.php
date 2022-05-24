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

		return ! empty($result) ? Schema::make(...$result) : null;
	}

	/**
	 * Create configuration key/value pair.
	 * 
	 * @param  \Ordnael\Configuration\Schema  $schema
	 * @return int|bool
	 */
	public function insert(Schema $schema)
	{
		$statement = $this->createInsertStatement(
			$this->table,
			$schema->key(),
			$schema->value(false),
			$schema->encrypted() ? '1' : '0'
		);

		if (self::getConnector()->connection()->exec(self::keepHistory($statement)) !== false) {
			return (int) self::getConnector()->connection()->lastInsertId('id');
		}

		return false;
	}

	/**
	 * Update value from configuration key.
	 * 
	 * @param  \Ordnael\Configuration\Schema  $schema
	 * @return bool
	 */
	public function update(Schema $schema)
	{
		$statement = $this->createUpdateStatement(
			$this->table,
			$schema->key(),
			$schema->value(false),
			$schema->encrypted() ? '1' : '0'
		);

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
