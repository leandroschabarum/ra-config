<?php

if (! function_exists('missing_ext')) {
	function missing_ext(string $ext) {
		return ! extension_loaded($ext);
	}
}
