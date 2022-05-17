<?php

namespace Ordnael\Configuration\Remote\Interfaces;

interface ConnectorInterface
{
	/**
	 * Establish a database connection.
	 * 
	 * @return \PDO
	 */
	public function connect();
	
	/**
	 * Close the database connection.
	 * 
	 * @return void
	 */
	public function close();
}
