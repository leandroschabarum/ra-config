<?php declare(strict_types=1);

namespace Ordnael\Configuration\Tests;

use PHPUnit\Framework\TestCase;
use Ordnael\Configuration\Schema;
use Ordnael\Configuration\Exceptions\SchemaFieldNotFoundException;

/**
 * @test
 * @testdox Unit test for configuration schema builder.
 */
final class SchemaTest extends TestCase
{
	/**
	 * @testdox Schema object constructor is protected.
	 */
	public function testSchemaObjectConstructorIsProtected()
	{
		$this->expectError();
		$this->expectErrorMessageMatches('%.*Call to protected .*::__construct\(\).*%');
		$this->assertNull(new Schema("app.name", "MyApp"));
	}

	/**
	 * @testdox Tests schema object creation without encryption.
	 */
	public function testSchemaWithoutEncryption()
	{
		$obj = Schema::make("app.name", "MyApp");
		$this->assertIsObject($obj);
		$this->assertInstanceOf(Schema::class, $obj);

		// Attempting to access 'value' field should throw an exception
		foreach (['context', 'key', 'value', 'encrypted'] as $field) {
			try {
				$obj->{$field};
			} catch (SchemaFieldNotFoundException $error) {
				continue;
			}

			$this->assertTrue(false, "No exception was thrown!");
		}

		// Assert that 'value' field is being assigned properly
		$this->assertEquals($obj->value(), "MyApp");
		$obj->dropSchemaCache();

		// Visual indication for Schema class string representation
		echo "\n\t{$obj}\n\n"; // Visualization only
	}

	/**
	 * @testdox Tests schema object creation with encryption.
	 */
	public function testSchemaWithEncryption()
	{
		$obj = Schema::make("app.pass", "admin@app", true);
		$this->assertIsObject($obj);
		$this->assertInstanceOf(Schema::class, $obj);

		// Attempting to access 'value' field should throw an exception
		foreach (['context', 'key', 'value', 'encrypted'] as $field) {
			try {
				$obj->{$field};
			} catch (SchemaFieldNotFoundException $error) {
				continue;
			}

			$this->assertTrue(false, "No exception was thrown!");
		}

		// Assert that encryption of 'value' field is working
		$this->assertNotEquals($obj->value(false), "admin@app");
		$this->assertEquals($obj->value(), "admin@app");
		$obj->dropSchemaCache();

		// Visual indication for Schema class string representation
		echo "\n\t{$obj}\n\n"; // Visualization only
	}
}
