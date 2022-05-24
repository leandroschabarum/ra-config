<?php

namespace Ordnael\Configuration\Remote\Interfaces;

use Ordnael\Configuration\Schema;

interface CrudInterface
{
	/**
	 * Method to read configuration key.
	 * 
	 * @param  string  $key
	 * @return \Ordnael\Configuration\Schema|null
	 */
	public function select(string $key);

	/**
	 * Method to create configuration key/value pair.
	 * 
	 * @param  @param  \Ordnael\Configuration\Schema  $schema
	 * @return int|bool
	 */
	public function insert(Schema $schema);

	/**
	 * Method to update value from configuration key.
	 * 
	 * @param  \Ordnael\Configuration\Schema  $schema
	 * @return bool
	 */
	public function update(Schema $schema);

	/**
	 * Method to delete configuration key/value pair.
	 * 
	 * @param  string  $key
	 * @return bool
	 */
	public function remove(string $key);
}
