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

		if ($this->log_is_enabled()) {
			$msg = str_repeat('  ', $this->indentation) . $msg;
			foreach (explode("\n", $msg) as $msg) {
				if ($this->php_error_log_enabled()) {
					error_log($msg);
				}
				$logfile = $this->log_filename();
				if ($logfile) {
					file_put_contents($logfile, $msg . "\n", FILE_APPEND);
				}
			}
		}
	}

	function log_filename() {
		if (!$this->log_is_enabled()) return false;
		if (!defined('WPRO_DEBUG_LOGFILE')) return false;
		if (WPRO_DEBUG_LOGFILE) {
			if (!file_exists(WPRO_DEBUG_LOGFILE)) {
				$touched = touch(WPRO_DEBUG_LOGFILE);
				if ($touched) {
					chmod(WPRO_DEBUG_LOGFILE, 0666); // 0666, if web browser user and unit test user are not the same.
					return WPRO_DEBUG_LOGFILE;
				}
				return false; // Could not create
			}
			return WPRO_DEBUG_LOGFILE;
		}
		return false;
	}

	function php_error_log_enabled() {
		if (!$this->log_is_enabled()) return false;
		if (!defined('WPRO_DEBUG_PHPERRORLOG')) return true;
		return WPRO_DEBUG_PHPERRORLOG;
	}

	function log_is_enabled() {
		return defined('WPRO_DEBUG') && WPRO_DEBUG;
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
