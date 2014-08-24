<?php

// Returns the system temporary dir, or any temporary dir we may be able to use:
function wpro_sysTmpDir() {
	if (!function_exists('sys_get_temp_dir')) {
		$tmp = '/tmp';
		if ($t = getenv('TMP'))  $tmp = $t;
		if ($t = getenv('TMPDIR')) $tmp = $t;
		if ($t = getenv('TEMP')) $tmp = $t;
	} else {
		$tmp = sys_get_temp_dir();
	}
	if (substr($tmp, -1) == '/') $tmp = substr($tmp, 0, -1);

	// TODO: This should be overrided by option.

	return $tmp;
}

// temporary directory for this request only: ( == base dir )
function wpro_reqTmpDir() {
	return wpro_sysTmpDir() . '/wpro' . time() . rand(0, 999999);
}
