<?php

namespace Ordnael\Configuration\Remote\Interfaces;

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
	 * @param  string  $key
	 * @return \Ordnael\Configuration\Schema|null
	 */
	public function insert(string $key, $value);

	/**
	 * Method to update value from configuration key.
	 * 
	 * @param  string  $key
	 * @return bool
	 */
	public function update(string $key, $value);

	/**
	 * Method to delete configuration key/value pair.
	 * 
	 * @param  string  $key
	 * @return bool
	 */
	public function remove(string $key);
}
