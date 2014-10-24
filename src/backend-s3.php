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

		add_filter('wpro_backend_retrieval_baseurl', array($this, 'url'));
	}

	function url($value) {
		$protocol = 'http';
		if (wpro()->options->get('wpro-aws-ssl')) {
			$protocol = 'https';
		}

		# this needs some more testing, but it seems like we have to use the
		# virtual-hosted-style for US Standard region, and the path-style
		# for region-specific endpoints:
		# (however we used the virtual-hosted style for everything before,
		# and that did work, so something has changed at amazons end.
		# is there any difference between old and new buckets?)
		if (wpro()->options->get('wpro-aws-endpoint') == 's3.amazonaws.com') {
			$url = $protocol . '://' . trim(str_replace('//', '/', wpro()->options->get('wpro-aws-bucket') . '.s3.amazonaws.com/' . trim(wpro()->options->get('wpro-folder'))), '/');
		} else {
			$url = $protocol . '://' . trim(str_replace('//', '/', wpro()->options->get('wpro-aws-endpoint') . '/' . wpro()->options->get('wpro-aws-bucket') . '/' . trim(wpro()->options->get('wpro-folder'))), '/');
		}

		return $url;
	}
		

}

function wpro_setup_s3_backend() {
	$wpro_backend_s3 = new WPRO_Backend_S3();
	wpro()->backends->register($wpro_backend_s3);
}
add_action('wpro_setup_backends', 'wpro_setup_s3_backend');

