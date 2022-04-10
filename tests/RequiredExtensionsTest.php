<?php declare(strict_types=1);

namespace Ordnael\Configuration\Tests;

use PHPUnit\Framework\TestCase;

/**
 * @test
 * @testdox Unit test for required database extensions.
 */
final class RequiredExtensionsTest extends TestCase
{
	const SUP_DB_EXT = [
		'mysqli',
		'pgsql',
		'sqlite3',
		'mongodb',
		'redis',
		'memcache',
		'memcached'
	];

	const SUP_CRYPT_EXT = [
		'sodium'
	];

	/**
	 * Variable to store database extension status.
	 * 
	 * @var array<string, boolean>
	 */
	private static $db_ext;

	/**
	 * Variable to store cryptography extension status.
	 * 
	 * @var array<string, boolean>
	 */
	private static $crypt_ext;

	/**
	 * @testdox Check if cryptography extensions are installed.
	 */
	public function testCryptographyExtensions()
	{
		$this->assertNotEmpty(self::SUP_CRYPT_EXT ?? []);

		foreach (self::SUP_CRYPT_EXT as $ext) {
			self::$crypt_ext[$ext] = extension_loaded($ext);
		}
	}

	/**
	 * MUST BE THE LAST TEST TO RUN FOR CRYPTOGRAPHY SUPPORT
	 * @testdox Checks if all cryptography extensions are present.
	 */
	public function testCryptographyFailure()
	{
		$pad_size = max(array_map('strlen', self::SUP_DB_EXT)) + 15;
		$header = "This test fails if there are NO available supported extensions* installed.\n";
		$checklist = "[[ Cryptography Extensions checklist ]]\n";
		$footer = <<< EOF
		\t*If the extensions are not marked as present in the
		\tchecklist, please install and configure them first.\n\n
		EOF;

		foreach (self::$crypt_ext as $extension => $status) {
			$name = str_pad($extension, $pad_size, '.');
			$emoji = chr(hexdec('E2')) . chr(hexdec('9C')) . ($status ? chr(hexdec('94')) : chr(hexdec('96')));
			$state = $status ? 'Present' : 'Missing';
			$checklist .= sprintf("\t | %s %s%s |\n", $emoji, $name, $state);
		}

		echo "\n\t{$header}\n\t{$checklist}\n{$footer}";
		$this->assertContains(true, self::$crypt_ext);
	}

	/**
	 * @testdox Check if database extensions are installed.
	 */
	public function testDatabaseExtensions()
	{
		$this->assertNotEmpty(self::SUP_DB_EXT ?? []);

		foreach (self::SUP_DB_EXT as $ext) {
			self::$db_ext[$ext] = extension_loaded($ext);
		}
	}

	/**
	 * MUST BE THE LAST TEST TO RUN FOR DATABASE SUPPORT
	 * @testdox Checks if at least one database extension is present.
	 */
	public function testDatabaseFailure()
	{
		$pad_size = max(array_map('strlen', self::SUP_DB_EXT)) + 11;
		$header = "This test ONLY fails if there are NO available supported extensions* installed.\n";
		$checklist = "[[ Database Extensions checklist ]]\n";
		$footer = <<< EOF
		\t*If the extension for your database is not marked as present
		\tin the checklist, please install and configure it first.\n\n
		EOF;

		foreach (self::$db_ext as $extension => $status) {
			$name = str_pad($extension, $pad_size, '.');
			$emoji = chr(hexdec('E2')) . chr(hexdec('9C')) . ($status ? chr(hexdec('94')) : chr(hexdec('96')));
			$state = $status ? 'Present' : 'Missing';
			$checklist .= sprintf("\t | %s %s%s |\n", $emoji, $name, $state);
		}

		echo "\n\t{$header}\n\t{$checklist}\n{$footer}";
		$this->assertContains(true, self::$db_ext);
	}
}
