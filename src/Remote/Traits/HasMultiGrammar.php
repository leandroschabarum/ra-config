<?php

namespace Ordnael\Configuration\Remote\Traits;

trait HasMultiGrammar
{
	/**
	 * MySQL/MariaDB create table statement.
	 * 
	 * @param  string  $name
	 * @param  bool    $fresh
	 * @return string
	 */
	protected static function createMySqlTableStatement(string $name, bool $fresh = false)
	{
		$statement = $fresh ? "DROP TABLE IF EXISTS `{$name}`;\n" : "";

		$statement .= <<<EOF
		CREATE TABLE IF NOT EXISTS `{$name}` (
			`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			`key` VARCHAR(255) NOT NULL UNIQUE,
			`value` TEXT,
			`encrypted` TINYINT(1) DEFAULT '0' CHECK( `encrypted` = '0' OR `encrypted` = '1'),
			PRIMARY KEY( `id` )
		);
		EOF;

		return $statement;
	}

	/**
	 * PostgreSQL create table statement.
	 * 
	 * @param  string  $name
	 * @param  bool    $fresh
	 * @return string
	 */
	protected static function createPostgreSqlTableStatement(string $name, bool $fresh = false)
	{
		$statement = $fresh ? "DROP TABLE IF EXISTS `{$name}`;\n" : "";
		
		$statement .= <<<EOF
		CREATE TABLE IF NOT EXISTS \"{$name}\" (
			\"id\" SERIAL,
			\"key\" VARCHAR(255) NOT NULL UNIQUE,
			\"value\" TEXT,
			\"encrypted\" BOOLEAN DEFAULT '0' CHECK( \"encrypted\" = '0' OR \"encrypted\" = '1'),
			PRIMARY KEY( \"id\" )
		);
		EOF;

		return $statement;
	}

	/**
	 * SQL Server create table statement.
	 * 
	 * @param  string  $name
	 * @param  bool    $fresh
	 * @return string
	 */
	protected static function createSqlServerTableStatement(string $name, bool $fresh = false)
	{
		$statement = $fresh ? "DROP TABLE IF EXISTS `{$name}`;\n" : "";

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

		return $statement;
	}
}
