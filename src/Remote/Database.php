<?php

namespace Ordnael\Configuration\Remote;

use Ordnael\Configuration\Remote\Interfaces\CrudInterface;
use Ordnael\Configuration\Remote\Traits\HasQueries;
use Ordnael\Configuration\Schema;

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
		$query = 'SELECT * FROM :from WHERE key = :key';
		$c = Connector::getConnector();

		$c->connection()->prepare($query);
		$c->connection()->bindParam(':from', self::from(), PDO::PARAM_STR);
		$c->connection()->bindParam(':key', $key, PDO::PARAM_STR);

		$c->connection()->execute();
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
