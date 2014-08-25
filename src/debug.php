<?php

// wpro_clean_debug_cache() and wpro_is_in_debug_cache() is used by the unit testing.

function wpro_clean_debug_cache() {
	global $wpro_debug_cache;
	$wpro_debug_cache = array();
}
wpro_clean_debug_cache();

function wpro_is_in_debug_cache($str) {
	global $wpro_debug_cache;
	return in_array(trim($str), $wpro_debug_cache);
}

function wpro_debug($msg) {

	global $wpro_debug_cache;
	$wpro_debug_cache[] = trim($msg);

	if (defined('WPRO_DEBUG') && WPRO_DEBUG) {
		$fh = fopen('/tmp/wpro-debug', 'a');
		fwrite($fh, trim($msg) . "\n");
		fclose($fh);
	}
}

