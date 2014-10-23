<?php

// wpro_clean_debug_cache() and wpro_is_in_debug_cache() is used by the unit testing.

if (!defined('ABSPATH')) exit();

class WPRO_Debug {

	var $debug_cache;
	
	function __construct() {
		$this->clean_debug_cache();
	}

	function clean_debug_cache() {
		$this->debug_cache = array();
	}

	function is_in_cache($str) {
		return in_array(trim($str), $this->debug_cache);
	}

	function log($msg) {

		$this->debug_cache[] = trim($msg);

		if (defined('WPRO_DEBUG') && WPRO_DEBUG) {
			error_log($msg);
		}
	}

}
