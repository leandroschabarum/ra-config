<?php

if (! function_exists('missing_extension')) {
	function missing_extension(string $ext) {
		return ! extension_loaded($ext);
	}
}

if (! function_exists('cache_decrypted_values')) {
	/**
	 * Helper function to check whether to cache values
	 * in their decrypted state to reduce decryption
	 * function calls or to store them encrypted.
	 * From environment variable:
	 * 
	 *   RA_CONFIG_CACHE_DECRYPTED_VALUES='true|false'
	 * 
	 * @return bool
	 */
	function cache_decrypted_values() {
		$env_opt = getenv('RA_CONFIG_CACHE_DECRYPTED_VALUES', true) ?: null;

		return preg_match('%^(true)$%i', $env_opt) ? true : false;
	}
}

if (! function_exists('config')) {
	function config(string $key, $default = null) {
		//
	}
}
