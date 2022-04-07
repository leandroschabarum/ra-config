<?php declare(strict_types=1);

namespace Ordnael\Configuration\Tests;
//require_once __DIR__.'/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Ordnael\Configuration\Schema;

/**
 * Schema class copy with public visibility for tests.
 */
class PublicSchema extends Schema
{
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
	 * @testdox Tests schema object with public constructor.
	 */
	public function testPublicSchemaContructor()
	{
		$obj = new PublicSchema("app.name", "MyApp");
		$this->assertIsObject($obj);
		$this->assertInstanceOf(Schema::class, $obj);

		$allowed = Schema::fields();

		if (($i = array_search('value', $allowed)) !== false) unset($allowed[$i]);

		foreach ($allowed as $attr) {
			$this->assertNotNull($obj->{$attr});
		}

		$this->assertEquals($obj->value(), "MyApp");

		echo "\n\t# {$obj->context}.{$obj->key}: {$obj->value()}\n"; // DEBUG
	}
}
