<?php

function wpro_debug($msg) {
	if (defined('WPRO_DEBUG') && WPRO_DEBUG) {
		$fh = fopen('/tmp/wpro-debug', 'a');
		fwrite($fh, trim($msg) . "\n");
		fclose($fh);
	}
}

