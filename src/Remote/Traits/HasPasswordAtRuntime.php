<?php

namespace Ordnael\Configuration\Remote\Traits;

trait HasPasswordAtRuntime
{
	/**
	 * Handles password retrieval from local environment at runtime.
	 * From environment variables:
	 * 
	 *   RA_CONFIG_DB_PASSWORD='password'
	 *   RA_CONFIG_DB_PASSWORD_ENCRYPTED='true:privkey|false'
	 * 
	 * @return string
	 */
	private function password()
	{
		$key = getenv('RA_CONFIG_DB_PASSWORD_ENCRYPTED', true) ?: null;

		// if ($key) {
		// 	openssl_private_decrypt(
		// 		getenv('RA_CONFIG_DB_PASSWORD', true) ?: null,
		// 		$password,
		// 		$key
		// 	);
		// } else {
		// 	$password = getenv('RA_CONFIG_DB_PASSWORD', true) ?: null;
		// }

		$password = getenv('RA_CONFIG_DB_PASSWORD', true) ?: null;

		return $password;
	}
}
