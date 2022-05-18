<?php

namespace Ordnael\Configuration\Remote;

use Ordnael\Configuration\Remote\Interfaces\CrudInterface;
use Ordnael\Configuration\Remote\Traits\HasQueries;
use Ordnael\Configuration\Exceptions\InvalidSchemaKeyException;
use Ordnael\Configuration\Schema;
use PDO;

/**
 * Database class for executing configuration operations.
 */
class Database extends Connector implements CrudInterface
{
	use HasQueries;

	const TABLE = 'config';

	/**
	 * Read configuration key.
	 * 
	 * @param  string  $key
	 * @return \Ordnael\Configuration\Schema|null
	 */
	public static function select(string $key)
	{
		if (! Schema::isValidKey($key)) throw new InvalidSchemaKeyException($key);

		$statement = sprintf("SELECT * FROM %s WHERE 'key' = '%s';", self::from(), $key);
		
		$db = self::getConnector();
		$result = $db->connection()->query($statement)->fetchAll(PDO::FETCH_NUM);
		$db->close();

		// Convert to Schema object
		return $result;
	}

	/**
	 * Create configuration key/value pair.
	 * 
	 * @param  string  $key
	 * @return \Ordnael\Configuration\Schema|null
	 */
	public static function insert(string $key, $value)
	{
		//
	}

	/**
	 * Update value from configuration key.
	 * 
	 * @param  string  $key
	 * @return bool
	 */
	public static function update(string $key, $value)
	{
		//
	}

	/**
	 * Delete configuration key/value pair.
	 * 
	 * @param  string  $key
	 * @return bool
	 */
	public static function remove(string $key)
	{
		//
	}
}
