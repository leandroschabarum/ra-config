<?php declare(strict_types=1);

namespace Ordnael\Configuration\Tests;
require_once __DIR__.'/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Ordnael\Configuration\Schema;
use Ordnael\Configuration\Tests\PublicSchema;

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
			$this->assertObjectNotHasAttribute($attr, $obj);
		}

		if (($i = array_search('value', $allowed)) !== false) unset($allowed[$i]);

		foreach ($allowed as $attr) {
			$this->assertNotNull($obj->{$attr});
		}

		$this->assertEquals($obj->value(), "MyApp");
		$obj->dropSchemaCache();
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
			$this->assertObjectNotHasAttribute($attr, $obj);
		}

		if (($i = array_search('value', $allowed)) !== false) unset($allowed[$i]);

		foreach ($allowed as $attr) {
			$this->assertNotNull($obj->{$attr});
		}

		$this->assertNotEquals($obj->value(false), "admin@app");
		$this->assertEquals($obj->value(), "admin@app");
		$obj->dropSchemaCache();
		echo "\n\t{$obj}\n\n"; // Visualization only
	}
}
