<?php

if (!defined('ABSPATH')) exit();

class WPRO_Options {

	private $option_keys = array(
		'wpro-service',
		'wpro-folder',
		'wpro-tempdir',
		'wpro-ftp-server',
		'wpro-ftp-user',
		'wpro-ftp-password',
		'wpro-ftp-pasvmode',
		'wpro-ftp-webroot'
	);

	function __construct() {
		add_action('init', array($this, 'init')); // Register the settings.
	}

	function get($option, $default = false) {
		if (!$this->is_an_option($option)) return null;

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

	function get_all_options() {
		$result = array();
		foreach ($this->option_keys as $key) {
			$result[$key] = $this->get_option($key);
		}
		return $result;
	}

	function init() {
		// Register all settings:
		foreach ($this->option_keys as $key) {
			add_site_option($key, '');
		};
	}

	function is_an_option($option) {
		return in_array($option, $this->option_keys);
	}

	function register($option) {
		if (!in_array($option, $this->option_keys)) {
			$this->option_keys[] = $option;
		}
	}

	function set($option, $value) {
		if (!$this->is_an_option($option)) return false;
		return update_site_option($option, $value);
	}

}
