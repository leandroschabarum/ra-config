<?php

namespace Ordnael\Configuration\Remote\Traits;

trait HasQueryHistory
{
	/**
	 * Stores the database query history.
	 * 
	 * @var array<int, string>
	 */
	private static $history = [];

	/**
	 * Add query string to history.
	 * 
	 * @param  string  $query
	 * @return string
	 */
	protected static function keepHistory(string $query)
	{
		self::$history[] = $query;

		return $query;
	}

	/**
	 * Retrieve executed queries history.
	 * 
	 * @return array<int, string>
	 */
	public static function getHistory()
	{
		return self::$history;
	}
}
