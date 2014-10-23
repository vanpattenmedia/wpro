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

		$this->backends = new WPRO_Backends();
		$this->debug = new WPRO_Debug();
		$this->options = new WPRO_Options();
		$this->tmpdir = new WPRO_TmpDir();
		$this->url = new WPRO_Url();

		add_action('after_setup_theme', array($this, 'init'));
	}

	function init() {
		do_action('wpro_setup_backends');

		add_filter('upload_dir', array($this->url, 'upload_dir')); // Sets the paths and urls for uploads.

		/*
		add_filter('wp_handle_upload', array($this, 'handle_upload')); // The very filter that takes care of uploads.
		add_filter('wp_generate_attachment_metadata', array($this, 'generate_attachment_metadata')); // We use this filter to store resized versions of the images.
		add_filter('wp_update_attachment_metadata', array($this, 'update_attachment_metadata')); // We use this filter to store resized versions of the images.
		add_filter('load_image_to_edit_path', array($this, 'load_image_to_edit_path')); // This filter downloads the image to our local temporary directory, prior to editing the image.
		add_filter('get_attached_file', array($this, 'load_image_to_local_path'), 10, 2); // This filter downloads the image to our local temporary directory, prior to using the image.
		add_filter('wp_save_image_file', array($this, 'save_image_file')); // Store image file.
		add_filter('wp_save_image_editor_file', array($this, 'save_image_file'), 10, 5);
		add_filter('wp_upload_bits', array($this, 'upload_bits')); // On XMLRPC uploads, files arrives as strings, which we are handling in this filter.
		add_filter('wp_handle_upload_prefilter', array($this, 'handle_upload_prefilter')); // This is where we check for filename dupes (and change them to avoid overwrites).
		add_filter('shutdown', array($this, 'shutdown'));
		*/

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

