<?php

if (! function_exists('missing_pecl')) {
	function missing_pecl(string $ext) {
		return ! extension_loaded($ext);
	}
}
