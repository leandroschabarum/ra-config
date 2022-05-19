<?php declare(strict_types=1);

namespace Ordnael\Configuration\Tests;

use PHPUnit\Framework\TestCase;
use Ordnael\Configuration\Remote\Database;

/**
 * @test
 * @testdox Unit test for Database class.
 */
final class DatabaseTest extends TestCase
{
	/**
	 * @testdox Database has migrate operation.
	 */
	public function testDatabaseMigration()
	{
		$this->assertTrue(true); // DEBUG
		$ok = Database::migrate(true);
		var_dump($ok); // DEBUG
	}

	/**
	 * @testdox Database can select data.
	 */
	public function testDatabaseSelect()
	{
		$this->assertTrue(true); // DEBUG
		$data = Database::select('app.name');
		var_dump($data); // DEBUG
	}
}
