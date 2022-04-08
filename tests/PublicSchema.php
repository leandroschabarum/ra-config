<?php

namespace Ordnael\Configuration\Tests;

use Ordnael\Configuration\Schema;

/**
 * Schema class copy with public visibility for tests.
 */
class PublicSchema extends Schema
{
	public static $schema_class = self::class;

	public function __construct($key, $val, $encrypted = false)
	{
		parent::__construct($key, $val, $encrypted);
	}

	public function value($decrypt = true)
	{
		return parent::value($decrypt);
	}

	public static function create($key, $val, $encrypted = false)
	{
		return parent::create($key, $val, $encrypted);
	}
}
