<?php

namespace Ordnael\Configuration\Remote\Traits;

use PDO;

trait HasConnectionOptions
{
	/**
	 * Default connection options.
	 * 
	 * @var array<string, mixed>
	 */
	private $options = [
		'default' => [
			PDO::ATTR_CASE              => PDO::CASE_NATURAL,
			PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_ORACLE_NULLS      => PDO::NULL_NATURAL,
			PDO::ATTR_STRINGIFY_FETCHES => false,
			PDO::ATTR_EMULATE_PREPARES  => false
		]
	];

	/**
	 * Retrieve connection options.
	 * 
	 * @param  string  $driver
	 * @return array<string, mixed>
	 */
	public function getOptions(string $driver = 'default')
	{
		switch ($driver) {
			case 'mysql':
				return $this->options['default'];

			case 'pgsql':
			case 'sqlite':
			case 'sqlsrv':
				// code...
				break;
		}

		return $this->options[$driver] ?? [];
	}
}
