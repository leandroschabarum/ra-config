<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @test
 * @testdox Unit test for required database extensions.
 */
final class RequiredExtensionsTest extends TestCase
{
	const SUP_EXT = ['mongodb', 'mysqli', 'redis', 'memcached'];

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
		$header = "This test ONLY fails if there are NO available supported extensions* installed.\n";
		$checklist = "[ Extensions checklist ]\n";
		$footer = <<< EOF
		\t*If the extension for your database is not marked as present
		\tin the checklist, please install and configure it first.\n
		EOF;

		foreach (self::$extensions as $extension => $status) {
			$emoji = chr(hexdec('E2')) . chr(hexdec('9C')) . ($status ? chr(hexdec('94')) : chr(hexdec('96')));
			$state = $status ? "present" : "missing";
			$checklist .= sprintf("\t- %s %s: %s\n", $emoji, $extension, $state);
		}

		echo "\n\t{$header}\n\t{$checklist}\n{$footer}";
		$this->assertContains(true, self::$extensions);
	}
}
