<?php
/**
Plugin Name: WP Read-Only
Plugin URI: http://wordpress.org/extend/plugins/wpro/
Description: Plugin for running your Wordpress site without Write Access to the web directory. Amazon S3 is used for uploads/binary storage. This plugin was made with cluster/load balancing server setups in mind - where you do not want your WordPress to write anything to the local web directory.
Version: 1.0
Author: alfreddatakillen
Author URI: http://nurd.nu/
License: GPLv2
 */

if (!defined('ABSPATH')) exit();

class WPRO_Core {

	private static $instance;
	private $log;

	// TODO: Tests to write: There must not be a __construct here. Since we are calling wpro() from the __constructs of objects created by WPRO_Core, we will end up in a loop creating multiple instances of WPRO_Core.

	function construct_singleton() { // Instead of __construct().
		foreach (glob(plugin_dir_path(__FILE__) . "src/*.php" ) as $file) {
			require_once($file);
		}

		$this->debug = new WPRO_Debug();
		$this->log = $this->debug->logblock("WPRO Instance");
		$this->log->log($_SERVER['REQUEST_METHOD'] . ' call to ' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$this->log->log("POST vars:\n" . var_export($_POST, true));
		}

		$this->admin = new WPRO_Admin();
		$this->backends = new WPRO_Backends();
		$this->cdn = new WPRO_CDN();
		$this->edit = new WPRO_Edit(); // Image editing functionality.
		$this->options = new WPRO_Options();
		$this->tmpdir = new WPRO_TmpDir();
		$this->uploads = new WPRO_Uploads();
		$this->url = new WPRO_Url();

		add_action('after_setup_theme', array($this, 'init_wp_hook'));
	}

	function __destruct() {
		$this->log->log("");
		return $this->log->logblockend();
	}

	function init_wp_hook() {
		do_action('wpro_setup_backend');
		do_action('wpro_setup_cdn');

		add_filter('upload_dir', array($this->url, 'upload_dir')); // Sets the paths and urls for uploads.

		$this->backends->activate_backend($this->options->get('wpro-service'));
	}

	/**
	* Initialize the singleton
	*/

	public static function instance() {
		if (!isset(self::$instance)) {
			self::$instance = new WPRO_Core();
			self::$instance->construct_singleton(); // Instead of __construct
		}
		return self::$instance;
	}

	/**
	* Prevent cloning
	*/

	function __clone() {
	}

	/**
	* Prevent unserializing
	*/

	function __wakeup() {
	}

}

/**
 * Allow direct access to WPRO classes
 */

function wpro() {
	return WPRO_Core::instance();
}

wpro();
