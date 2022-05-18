<?php

namespace Ordnael\Configuration\Remote\Traits;

trait HasQueries
{
	/**
	 * Retrieve FROM statement for SQL queries.
	 * 
	 * @return string
	 */
	public static function from()
	{
		// If TABLE constant is defined in the class
		// use it as the configuration table name
		$table = defined('self::TABLE') ? self::TABLE : 'configuration';
		$database = getenv('RA_CONFIG_DB_DATABASE', true) ?: null;

		return $database ? "{$database}.{$table}" : $table;
	}
}
