<?php declare(strict_types=1);

namespace Ordnael\Configuration\Tests;

use PHPUnit\Framework\TestCase;
use Ordnael\Configuration\Remote\Connector;

/**
 * @test
 * @testdox Unit test for Connector class.
 */
final class ConnectorTest extends TestCase
{
	/**
	 * @testdox Connector object constructor is private.
	 */
	public function testConnectorObjectConstructorIsPrivate()
	{
		$this->expectError();
		$this->expectErrorMessageMatches('%.*Call to private .*::__construct\(\).*%');
		$this->assertNull(new Connector());
	}

	/**
	 * @testdox Connector establishes database connection.
	 */
	public function testEstablishingDatabaseConnection()
	{
		$obj = Connector::getConnector();
		$this->assertInstanceOf(Connector::class, $obj);
		print_r("\t# {$obj->version()}"); // DEBUG
		$obj->close();
	}
}
