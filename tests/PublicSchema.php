<?php

namespace Ordnael\Configuration\Tests;

use Ordnael\Configuration\Schema;

/**
 * Schema class copy with public visibility for tests.
 */
class PublicSchema extends Schema
{
	public static $schema_class = self::class;

	public static $shm_cache_size = 1000; // bytes

	public static $shm_cache_expires = 15; // seconds

	public function __construct($key, $val, $encrypted = false)
	{
		parent::__construct($key, $val, $encrypted);

		$mock_data = [
			"test1" => 123,
			"test2" => "content",
			"test3" => ['x' => -1, 'y' => "something"],
			"test4" => (object) ['name' => "alexa", 'age' => 23],
			"test5" => 45.6
		];

		foreach ($mock_data as $key => $value) {
			$cache = $this->getFromCache($key);

			if ($cache === null) {
				echo "\t>>> Storing key {$key} in SHM cache...\n";
				$this->putInCache($key, $value);
			} else {
				echo "\t<<< Retrieved key {$key} from SHM cache.\n";
			}
		}

		$rand_key = array_rand($mock_data);

		if ($this->delFromCache($rand_key)) {
			echo "\t### Removed key {$rand_key} from SHM cache!\n";
		}
	}

	public function value($decrypt = true)
	{
		return parent::value($decrypt);
	}

	public function dropSchemaCache()
	{
		parent::dropSchemaCache();
	}

	public static function create($key, $val, $encrypted = false)
	{
		return parent::create($key, $val, $encrypted);
	}
}
