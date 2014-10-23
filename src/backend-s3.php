<?php

if (!defined('ABSPATH')) exit();

class WPRO_Backend_S3 {

	public $name;

	function __construct() {
		$this->name = 'Amazon S3';
	}

}


function wpro_setup_s3_backend() {
	$wpro_backend_s3 = new WPRO_Backend_S3();
	wpro()->backends->register($wpro_backend_s3);
}
add_action('wpro_setup_backends', 'wpro_setup_s3_backend');

