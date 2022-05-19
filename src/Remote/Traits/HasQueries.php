<?php

namespace Ordnael\Configuration\Remote\Traits;

trait HasQueries
{
	/**
	 * Retrieve FROM statement for SQL queries.
	 * 
	 * @param  bool $full
	 * @return string
	 */
	public static function from(bool $full = false)
	{
		// If TABLE constant is defined in the class
		// use it as the configuration table name
		$table = defined('self::TABLE') ? self::TABLE : 'configuration';

		if ($full) {
			$database = getenv('RA_CONFIG_DB_DATABASE', true) ?: null;

			return "{$database}.{$table}";
		}

		return $table;
	}
}
