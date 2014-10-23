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


	function __construct() {
		foreach (glob(plugin_dir_path(__FILE__) . "src/*.php" ) as $file) {
			require_once($file);
		}

		$this->debug = new WPRO_Debug();
		$this->options = new WPRO_Options();
		$this->tmpdir = new WPRO_TmpDir();
		$this->url = new WPRO_Url();
	}

	/**
	* Initialize the singleton
	*/

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WPRO_Core;
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

