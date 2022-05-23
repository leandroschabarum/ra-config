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
	 * @testdox Database can update data.
	 */
	public function testDatabaseUpdate()
	{
		$db = new Database();
		
		$ok = $db->update('app.name', ['x' => 11, 'y' => 17]);
		$db->close();

		$this->assertIsBool($ok);
		$this->assertTrue($ok);
	}

	/**
	 * @testdox Database can select data.
	 */
	public function testDatabaseSelect()
	{
		$n = 1000;
		$start_time = microtime(true);

		$db = new Database();
		
		for ($i=0; $i < $n; $i++) {
			$data = $db->select('app.name');
		}

		$db->close();
		$end_time = microtime(true);

		$execution_time = ($end_time - $start_time);
		echo "\t# Total time for {$n} SELECT statements: {$execution_time}s\n"; // DEBUG

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
		$this->markTestSkipped();
		$this->assertNotEmpty(Database::getHistory());

		print_r("\n" . implode("\n\n", Database::getHistory()));
	}
}
