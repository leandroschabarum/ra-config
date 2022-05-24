<?php

namespace Ordnael\Configuration\Remote\Traits;

use PDO;

trait HasMultiGrammar
{
	/**
	 * Create database table statement.
	 * 
	 * @param  string  $type
	 * @param  string  $name
	 * @param  bool    $fresh
	 * @return string|null
	 */
	protected static function createTableStatement(string $type, string $name, bool $fresh = false)
	{
		if (in_array($type, PDO::getAvailableDrivers())) {
			$statement = $fresh ? "DROP TABLE IF EXISTS \"{$name}\";\n" : "";

			switch ($type) {
				case 'mysql':
				case 'mariadb':
					$statement .= <<<EOF
					CREATE TABLE IF NOT EXISTS `{$name}` (
						`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
						`key` VARCHAR(255) NOT NULL UNIQUE,
						`value` TEXT,
						`encrypted` TINYINT(1) DEFAULT '0' CHECK( `encrypted` = '0' OR `encrypted` = '1'),
						PRIMARY KEY( `id` )
					);
					EOF;
					break;

				case 'pgsql':
					$statement .= <<<EOF
					CREATE TABLE IF NOT EXISTS \"{$name}\" (
						\"id\" SERIAL,
						\"key\" VARCHAR(255) NOT NULL UNIQUE,
						\"value\" TEXT,
						\"encrypted\" BOOLEAN DEFAULT '0' CHECK( \"encrypted\" = '0' OR \"encrypted\" = '1'),
						PRIMARY KEY( \"id\" )
					);
					EOF;
					break;

				case 'sqlsrv':
					$statement .= <<<EOF
					IF object_id('{$name}', 'U') is null
						CREATE TABLE {$name} (
							[id] INT NOT NULL IDENTITY,
							[key] VARCHAR(255) NOT NULL,
							[value] TEXT,
							[encrypted] INT CHECK( [encrypted] = 0 OR [encrypted] = 1),
							UNIQUE( [key] ),
							PRIMARY KEY( [id] )
						);
					EOF;
					break;

				case 'sqlite':
					$statement .= <<<EOF
					CREATE TABLE IF NOT EXISTS \"{$name}\" (
						\"id\" INTEGER PRIMARY KEY,
						\"key\" TEXT NOT NULL UNIQUE,
						\"value\" TEXT,
						\"encrypted\" INTEGER DEFAULT '0' CHECK( \"encrypted\" = '0' OR \"encrypted\" = '1')
					);
					EOF;
					break;
			}
		}

		return $statement ?? null;
	}

	/**
	 * Prepare select statement.
	 * 
	 * @param  string  $table
	 * @param  string  $key
	 * @return string
	 */
	protected function createSelectStatement(string $table, string $key)
	{
		return "SELECT \"key\", \"value\", \"encrypted\" FROM {$table} WHERE \"key\" = '{$key}';";
	}

	/**
	 * Prepare insert statement.
	 * 
	 * @param  string  $table
	 * @param  string  $key
	 * @param  mixed   $value
	 * @param  string  $encrypted
	 * @return string
	 */
	protected function createInsertStatement(string $table, string $key, $value, string $encrypted)
	{
		return "INSERT INTO {$table} (\"key\", \"value\", \"encrypted\") VALUES ('{$key}', '{$value}', '{$encrypted}');";
	}

	/**
	 * Prepare update statement.
	 * 
	 * @param  string  $table
	 * @param  string  $key
	 * @param  mixed   $value
	 * @param  string  $encrypted
	 * @return string
	 */
	protected function createUpdateStatement(string $table, string $key, $value, string $encrypted)
	{
		return "UPDATE {$table} SET \"value\" = '{$value}', \"encrypted\" = '{$encrypted}' WHERE \"key\" = '{$key}';";
	}

	/**
	 * Prepare delete statement.
	 * 
	 * @param  string  $table
	 * @param  string  $key
	 * @return string
	 */
	protected function createDeleteStatement(string $table, string $key)
	{
		return "DELETE FROM {$table} WHERE \"key\" = '{$key}';";
	}
}
