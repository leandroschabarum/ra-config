<?php declare(strict_types=1);

namespace Ordnael\Configuration\Tests;

use PHPUnit\Framework\TestCase;
use Ordnael\Configuration\Schema;
use Ordnael\Configuration\Tests\PublicSchema;
use Ordnael\Configuration\Exceptions\SchemaFieldNotFoundException;

/**
 * @test
 * @testdox Unit test for configuration schema builder.
 */
final class SchemaBuildingTest extends TestCase
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
	 * @testdox Schema create method is protected.
	 */
	public function testSchemaCreateMethodIsProtected()
	{
		$this->expectError();
		$this->expectErrorMessageMatches('%.*Call to protected .*::create\(\).*%');
		$this->assertNull(Schema::create("app.name", "MyApp"));
	}

	/**
	 * @testdox Tests schema object with public constructor and no encryption.
	 */
	public function testPublicSchemaContructorWithoutEncryption()
	{
		$obj = new PublicSchema("app.name", "MyApp");
		$this->assertIsObject($obj);
		$this->assertInstanceOf(Schema::class, $obj);

		$allowed = Schema::fields();

		foreach ($allowed as $attr) {
			// Make sure that none off the Schema fields
			// are visible from the object's perspective
			$this->assertObjectNotHasAttribute($attr, $obj);
		}

		// Attempting to access 'value' field should throw an exception
		try {
			$obj->value;
		} catch (SchemaFieldNotFoundException $error) {
			$emoji = chr(hexdec('E2')) . chr(hexdec('9C')) . chr(hexdec('94'));
			echo "\t{$emoji} Caught Exception: {$error->getMessage()}";
		} finally {
			$emoji = chr(hexdec('E2')) . chr(hexdec('9C')) . chr(hexdec('96'));
			$this->assertTrue(isset($error), "{$emoji} No Exception was throwned!");
		}

		// Field 'value' is unset in order to verify access to other fields
		if (($i = array_search('value', $allowed)) !== false) unset($allowed[$i]);

		foreach ($allowed as $attr) {
			// Method overloading should allow for reading of private fields
			$this->assertNotNull($obj->{$attr});
		}

		// Assert that 'value' field is being assigned properly
		$this->assertEquals($obj->value(), "MyApp");
		$obj->dropSchemaCache();
		// Visual indication for Schema class string representation
		echo "\n\t{$obj}\n\n"; // Visualization only
	}

	/**
	 * @testdox Tests schema object with public constructor and encryption.
	 */
	public function testPublicSchemaContructorWithEncryption()
	{
		$obj = PublicSchema::create("app.pass", "admin@app", true);
		$this->assertIsObject($obj);
		$this->assertInstanceOf(PublicSchema::class, $obj);

		$allowed = Schema::fields();

		foreach ($allowed as $attr) {
			// Make sure that none off the Schema fields
			// are visible from the object's perspective
			$this->assertObjectNotHasAttribute($attr, $obj);
		}

		// Attempting to access 'value' field should throw an exception
		try {
			$obj->value;
		} catch (SchemaFieldNotFoundException $error) {
			$emoji = chr(hexdec('E2')) . chr(hexdec('9C')) . chr(hexdec('94'));
			echo "\t{$emoji} Caught Exception: {$error->getMessage()}";
		} finally {
			$emoji = chr(hexdec('E2')) . chr(hexdec('9C')) . chr(hexdec('96'));
			$this->assertTrue(isset($error), "{$emoji} No Exception was throwned!");
		}

		// Field 'value' is unset in order to verify access to other fields
		if (($i = array_search('value', $allowed)) !== false) unset($allowed[$i]);

		foreach ($allowed as $attr) {
			// Method overloading should allow for reading of private fields
			$this->assertNotNull($obj->{$attr});
		}

		// Assert if encryption of 'value' field is working
		$this->assertNotEquals($obj->value(false), "admin@app");
		$this->assertEquals($obj->value(), "admin@app");
		$obj->dropSchemaCache();
		// Visual indication for Schema class string representation
		echo "\n\t{$obj}\n\n"; // Visualization only
	}
}
