<?php

class WPRO_Gravityforms {

	function __construct() {
		add_action('gform_after_submission', array($this, 'gravityforms_after_submission'), 10, 2);
	}

	function gravityforms_after_submission($entry, $form) {
		wpro()->debug->log('WPRO::gravityforms_after_submission($entry, $form);');

		$upload_dir = wp_upload_dir();
		foreach($form['fields'] as $field) {
			if ($field['type'] == 'fileupload') {
				$id = (int) $field['id'];
				$file_to_upload = $entry[$id];
				if($file_to_upload) {
					$url = $entry[$id];
					$file_to_upload = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $file_to_upload);
					$mime = wp_check_filetype($file_to_upload);

					$response = $this->backend->upload($file_to_upload, $url, $mime['type']);
					if (!$response) return false;
				}
			}
		}
	}
}

if (class_exists('GFCommon')) {
	$wpro_gravityforms = new WPRO_Gravityforms();
}
