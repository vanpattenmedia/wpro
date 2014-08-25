<?php

function wpro_all_option_keys() {
	return array(
		'wpro-service',
		'wpro-folder',
		'wpro-tempdir',
		'wpro-aws-key',
		'wpro-aws-secret',
		'wpro-aws-bucket',
		'wpro-aws-cloudfront',
		'wpro-aws-virthost',
		'wpro-aws-endpoint',
		'wpro-aws-ssl',
		'wpro-ftp-server',
		'wpro-ftp-user',
		'wpro-ftp-password',
		'wpro-ftp-pasvmode',
		'wpro-ftp-webroot'
	);
}

function wpro_get_all_options() {
	$result = array();
	foreach (wpro_all_option_keys() as $key) {
		$result[$key] = wpro_get_option($key);
	}
	return $result;
}

function wpro_is_an_option($option) {
	return in_array($option, wpro_all_option_keys());
}

function wpro_get_option($option, $default = false) {
	if (!wpro_is_an_option($option)) return null;

	if (!defined('WPRO_ON') || !WPRO_ON) {
		return get_site_option($option, $default);
	}
	$constantName = strtoupper(str_replace('-', '_', $option));
	if (defined($constantName)) {
		return constant($constantName);
	} else {
		return $default;
	}
}


