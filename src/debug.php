<?php

// wpro_clean_debug_cache() and wpro_is_in_debug_cache() is used by the unit testing.

if (!defined('ABSPATH')) exit();

class WPRO_Debug {

	var $debug_cache;
	var $indentation;

	function __construct() {
		$this->clean_debug_cache();
	}

	function clean_debug_cache() {
		$this->debug_cache = array();
	}

	function is_in_cache($str) {
		return in_array(trim($str), $this->debug_cache);
	}

	function logblock($msg) {
		$this->log($msg);
		$this->indentation++;
		return $this;
	}

	function logblockend() {
		$this->indentation--;
	}

	function logreturn($value) {
		$this->log('return: ' . var_export($value, true));
		$this->logblockend();
		return $value;
	}

	function log($msg) {

		$this->debug_cache[] = trim($msg);

		if (defined('WPRO_DEBUG') && WPRO_DEBUG) {
			foreach (explode("\n", $msg) as $msg) {
				error_log(str_repeat('  ', $this->indentation) . $msg);
			}
		}
	}

}
