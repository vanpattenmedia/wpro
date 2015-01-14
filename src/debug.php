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

		if (is_array($msg)) $msg = var_export($msg, true);

		$this->debug_cache[] = trim($msg);

		if (defined('WPRO_DEBUG') && WPRO_DEBUG) {
			foreach (explode("\n", $msg) as $msg) {
				error_log(str_repeat('  ', $this->indentation) . $msg);
			}
		}
	}

}

// Log upload errors:
if (!function_exists('wp_handle_upload_error')) {
	function wp_handle_upload_error( &$file, $message) {
		$log = wpro()->debug->logblock('wp_handle_upload_error()');
		$log->log('$file = ' . var_export($file, true));
		$log->log('$message = ' . var_export($message, true));
		if (file_exists($file['tmp_name'])) {
			$log->log('Temporary file still exists.');
			$log->log('File size: ' . filesize($file['tmp_name']));
		} else {
			$log->log('Temporary file does not exist (anymore).');
		}
		$upload_dir = wp_upload_dir();
		if (!is_dir($upload_dir['basedir'])) {
			if (file_exists($upload_dir['basedir'])) {
				$log->log('Upload basedir is NOT a directory: ' . $upload_dir['basedir']);
			} else {
				$log->log('Upload basedir DOES NOT exist: ' . $upload_dir['basedir']);
			}
		} else {
			$log->log('Upload basedir exists: ' . $upload_dir['basedir']);
		}
		return $log->logreturn(array('error' => $message));
	}
}
