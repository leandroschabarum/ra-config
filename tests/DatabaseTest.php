<?php declare(strict_types=1);

namespace Ordnael\Configuration\Tests;

use PHPUnit\Framework\TestCase;
use Ordnael\Configuration\Remote\Database;
use Ordnael\Configuration\Schema;

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

		$this->assertIsBool($ok);
		$this->assertTrue($ok);
	}

	/**
	 * @testdox Database can insert data.
	 */
	public function testDatabaseInsert()
	{
		$db = new Database();
		
		$id = $db->insert('app.name', ['x' => 10, 'y' => 17]);
		$db->close();

		$this->assertIsInt($id);
	}

	/**
	 * @testdox Database can select data.
	 */
	public function testDatabaseSelect()
	{
		$db = new Database();
		
		$data = $db->select('app.name');
		$db->close();

		$this->assertInstanceOf(Schema::class, $data);
		echo "\n\t{$data}\n\n"; // Visualization only
	}

	/**
	 * @testdox Database can remove data.
	 */
	public function testDatabaseRemove()
	{
		$db = new Database();
		
		$ok = $db->remove('app.name');
		$db->close();

		$this->assertIsBool($ok);
		$this->assertTrue($ok);
	}

	/**
	 * @testdox Database has query history.
	 */
	public function testDatabaseQueryHistory()
	{
		$this->assertNotEmpty(Database::getHistory());

		print_r("\n" . implode("\n\n", Database::getHistory()));
	}
}
