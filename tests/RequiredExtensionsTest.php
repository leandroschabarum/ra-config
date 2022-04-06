<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @test
 * @testdox Unit test for required database extensions.
 */
final class RequiredExtensionsTest extends TestCase
{
	const SUP_EXT = [
		'mysqli',
		'pgsql',
		'sqlite3',
		'mongodb',
		'redis',
		'memcache',
		'memcached'
	];

	/**
	 * Variable to store extension status.
	 * 
	 * @var array<string, boolean>
	 */
	private static $extensions;

	/**
	 * @testdox Check if database extensions are installed.
	 */
	public function testExtensions()
	{
		$this->assertNotEmpty(self::SUP_EXT ?? []);

		foreach (self::SUP_EXT as $ext) {
			self::$extensions[$ext] = extension_loaded($ext);
		}
	}

	/**
	 * MUST BE THE LAST TEST TO RUN
	 * @testdox Checks if at least one database extension is present.
	 */
	public function testFailure()
	{
		$pad_size = max(array_map('strlen', self::SUP_EXT)) + 8;
		$header = "This test ONLY fails if there are NO available supported extensions* installed.\n";
		$checklist = "[[ Extensions checklist ]]\n";
		$footer = <<< EOF
		\t*If the extension for your database is not marked as present
		\tin the checklist, please install and configure it first.\n
		EOF;

		foreach (self::$extensions as $extension => $status) {
			$name = str_pad($extension, $pad_size, '.');
			$emoji = chr(hexdec('E2')) . chr(hexdec('9C')) . ($status ? chr(hexdec('94')) : chr(hexdec('96')));
			$state = $status ? 'Present' : 'Missing';
			$checklist .= sprintf("\t%s %s%s\n", $emoji, $name, $state);
		}

		echo "\n\t{$header}\n\t{$checklist}\n{$footer}";
		$this->assertContains(true, self::$extensions);
	}
}
