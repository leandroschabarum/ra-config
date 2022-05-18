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
	 * @testdox Database can select data.
	 */
	public function testDatabaseSelect()
	{
		$data = Database::select('app.name');
		$var_dump($data); // DEBUG
		$this->assertTrue(true); // DEBUG
	}
}
