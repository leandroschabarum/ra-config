<?php

namespace Ordnael\Configuration\Remote;

use Ordnael\Configuration\Remote\Interfaces\CrudInterface;
use Ordnael\Configuration\Schema;

/**
 * Database class for executing configuration operations.
 */
class Database extends Connector implements CrudInterface
{
	/**
	 * Holds the name of the database table.
	 * 
	 * @var string
	 */
	protected $table = 'config';

	/**
	 * Read configuration key.
	 * 
	 * @param  string  $key
	 * @return \Ordnael\Configuration\Schema|null
	 */
	public function select(string $key)
	{
		//
	}

	/**
	 * Create configuration key/value pair.
	 * 
	 * @param  string  $key
	 * @return \Ordnael\Configuration\Schema|null
	 */
	public function insert(string $key, $value)
	{
		//
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
		//
	}
}
