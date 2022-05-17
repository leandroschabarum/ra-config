<?php

namespace Ordnael\Configuration\Remote\Interfaces;

interface ConnectorInterface
{
	/**
	 * Retrieve database connection.
	 * 
	 * @return \PDO
	 */
	public static function getConnection();

	/**
	 * Close the database connection.
	 * 
	 * @return void
	 */
	public static function close();

	/**
	 * Establish a database connection.
	 * 
	 * @return \PDO
	 */
	public function connect();
}
