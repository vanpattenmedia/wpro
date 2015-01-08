<?php

if (!defined('ABSPATH')) exit();

class WPRO_Edit {

	function __construct() {
		$log = wpro()->debug->logblock('WPRO_Edit::__construct()');

		add_filter('wp_save_image_file', array($this, 'save_image_file')); // Store image file.
		add_filter('wp_save_image_editor_file', array($this, 'save_image_editor_file'), 10, 5);

		return $log->logreturn(true);
	}

	function save_image_file($dummy, $filename, $image, $mime_type, $post_id) {
		$log = wpro()->debug->logblock('WPRO_Uploads::save_image_file($dummy = "' . $dummy . '", $filename = "' . $filename . '", $image, $mime_type = "' . $mime_type . '", $post_id = ' . $post_id .')');
		return $log->logreturn($this->save_image_editor_file($dummy, $filename, $image, $mime_type, $post_id));
	}


	function save_image_editor_file($dummy, $filename, $image, $mime_type, $post_id) {

		// This function is called when an image has been edited.
		// The $image variable is the ImageMagick data object of the new image.

		$log = wpro()->debug->logblock('WPRO_Uploads::save_image_editor_file($dummy = "' . $dummy . '", $filename = "' . $filename . '", $image, $mime_type = "' . $mime_type . '", $post_id = ' . $post_id .')');
		if (!wpro()->backends->is_backend_activated()) {
			$log->log('Backend not activated.');
			return $log->logreturn(null);
		}

		$reqTmpDir = wpro()->tmpdir->reqTmpDir();

		if (substr($filename, 0, strlen($reqTmpDir)) != $reqTmpDir) return $log->logreturn(false);
		$tmpfile = substr($filename, strlen($reqTmpDir));
		if (!preg_match('/^wpro[0-9]+(\/.+)$/', $tmpfile, $regs)) return $log->logreturn(false);

		$tmpfile = $regs[1];

		$image->save($filename, $mime_type);

		$upload = wp_upload_dir();
		$url = $upload['baseurl'];
		if (substr($url, -1) != '/') $url .= '/';
		while (substr($tmpfile, 0, 1) == '/') $tmpfile = substr($tmpfile, 1);
		$url .= $tmpfile;

		return $log->logreturn(wpro()->backends->active_backend->upload($filename, wpro()->url->normalize($url), $mime_type));

	}


}
