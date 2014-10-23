<?php

if (!defined('ABSPATH')) exit();

class WPRO_TmpDir {

	private $reqTmpDirCache = '.';

	function __construct() {
		add_filter('shutdown', array($this, 'cleanUp')); // Remove temporary directory
	}

	// Returns the system temporary dir, or any temporary dir we may be able to use:
	function sysTmpDir() {

		$tmp = wpro()->options->get('wpro-tempdir');

		if (!is_string($tmp) || strlen($tmp) < 1) {
			if (!function_exists('sys_get_temp_dir')) {
				$tmp = '/tmp';
				if ($t = getenv('TMP'))  $tmp = $t;
				if ($t = getenv('TMPDIR')) $tmp = $t;
				if ($t = getenv('TEMP')) $tmp = $t;
			} else {
				$tmp = sys_get_temp_dir();
			}
		}

		if (substr($tmp, -1) == '/') $tmp = substr($tmp, 0, -1);

		return $tmp;
	}

	// temporary directory for this request only:
	function reqTmpDir() {
		if ($this->reqTmpDirCache !== '.') return $this->reqTmpDirCache;
		while (is_dir($this->reqTmpDirCache)) $this->reqTmpDirCache = $this->sysTmpDir() . '/wpro' . time() . rand(0, 999999);
		return $this->reqTmpDirCache;

	}

	function rmdirRecursive($dir) {
		$files = array_diff(scandir($dir), array('.', '..'));
		foreach ($files as $file) {
			if (is_dir($dir .'/' . $file)) {
				$this->rmdirRecursive($dir . '/' . $file);
			} else {
				unlink($dir . '/' . $file);
			}
		}
		return rmdir($dir);
	}

	function cleanUp() {
		if (is_dir($this->reqTmpDir())) {
			$this->rmdirRecursive($this->reqTmpDir());
		}
	}

}
