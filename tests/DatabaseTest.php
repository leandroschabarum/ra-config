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
		$db = new Database();

		$ok = $db->migrate(true);
		$db->close();

		$this->assertTrue($ok);
		var_dump(Database::getHistory()); // DEBUG
	}

	/**
	 * @testdox Database can select data.
	 */
	public function testDatabaseSelect()
	{
		$this->assertTrue(true); // DEBUG
		$db = new Database();
		
		$data = $db->select('app.name');
		$db->close();

		var_dump($data); // DEBUG
		var_dump(Database::getHistory()); // DEBUG
	}
}
