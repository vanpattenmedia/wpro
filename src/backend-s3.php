<?php

if (!defined('ABSPATH')) exit();

class WPRO_Backend_S3 {

	public $name;

	function __construct() {
		$this->name = 'Amazon S3';
		wpro()->options->register('wpro-aws-key');
		wpro()->options->register('wpro-aws-secret');
		wpro()->options->register('wpro-aws-bucket');
		wpro()->options->register('wpro-aws-cloudfront');
		wpro()->options->register('wpro-aws-virthost');
		wpro()->options->register('wpro-aws-endpoint');
		wpro()->options->register('wpro-aws-ssl');

		add_filter('wpro_backend_retrieval_protocol', array($this, 'retrieval_protocol'));
	}

	function retrieval_protocol($protocol) {
		if (wpro()->options->get('wpro-aws-ssl')) {
			return 'https';
		}
		return 'http';
	}

}

function wpro_setup_s3_backend() {
	$wpro_backend_s3 = new WPRO_Backend_S3();
	wpro()->backends->register($wpro_backend_s3);
}
add_action('wpro_setup_backends', 'wpro_setup_s3_backend');

