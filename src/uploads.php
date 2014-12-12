<?php

if (!defined('ABSPATH')) exit();

class WPRO_Uploads {

	function __construct() {
		add_filter('wp_handle_upload', array($this, 'handle_upload'));
		add_filter('wp_generate_attachment_metadata', array($this, 'generate_attachment_metadata')); // We use this filter to store resized versions of the images.
		add_filter('wp_update_attachment_metadata', array($this, 'update_attachment_metadata')); // We use this filter to store resized versions of the images.
		add_filter('load_image_to_edit_path', array($this, 'load_image_to_edit_path')); // This filter downloads the image to our local temporary directory, prior to editing the image.
		add_filter('get_attached_file', array($this, 'load_image_to_local_path'), 10, 2); // This filter downloads the image to our local temporary directory, prior to using the image.
		add_filter('wp_save_image_file', array($this, 'save_image_file')); // Store image file.
		add_filter('wp_save_image_editor_file', array($this, 'save_image_file'), 10, 5);
		add_filter('wp_upload_bits', array($this, 'upload_bits')); // On XMLRPC uploads, files arrives as strings, which we are handling in this filter.
		add_filter('wp_handle_upload_prefilter', array($this, 'handle_upload_prefilter')); // This is where we check for filename dupes (and change them to avoid overwrites).
	}

	function exists($path) {

		wpro()->debug->log('WPRO_Uploads::exists("' . $path . '");');

		$path = $this->wpro()->url->normalize($path);

		wpro()->debug->log('-> testing url: ' . $path);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_NOBODY, 1);
		curl_setopt($ch, CURLOPT_URL, $path);
		$result = trim(curl_exec_follow($ch));

		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		wpro()->debug->log('-> http return code: ' . $httpCode);

		if ($httpCode != 200) return false;

		return true;
	}

	function generate_attachment_metadata($data) {
		wpro()->debug->log('WPRO::generate_attachment_metadata();');
		if (!is_array($data) || !isset($data['sizes']) || !is_array($data['sizes'])) return $data;

		$upload_dir = wp_upload_dir();
		$filepath = $upload_dir['basedir'] . '/' . preg_replace('/^(.+\/)?.+$/', '\\1', $data['file']);
		foreach ($data['sizes'] as $size => $sizedata) {
			$file = $filepath . $sizedata['file'];
			$url = $upload_dir['baseurl'] . substr($file, strlen($upload_dir['basedir']));

			$mime = 'application/octet-stream';
			switch(substr($file, -4)) {
				case '.gif':
					$mime = 'image/gif';
					break;
				case '.jpg':
					$mime = 'image/jpeg';
					break;
				case '.png':
					$mime = 'image/png';
					break;
			}

			$this->backend->upload($file, $url, $mime);
		}

		return $data;
	}

	function handle_upload($data) {

		$data['url'] = wpro()->url->normalize($data['url']);
		if (!file_exists($data['file'])) return false; //TODO: Test what is happening in this situation.

		$response = wpro()->backends->active_backend()->upload($data['file'], $data['url'], $data['type']);
		$data = apply_filters('wpro_backend_handle_upload', $data);

		// One thing has changed here. Previously, we returned false from this function, when upload failed.
		// TODO: Check what is happening here on failing uploads.

		return $data;
	}

	// Handle duplicate filenames:
	// Wordpress never calls the wp_handle_upload_overrides filter properly, so we do not have any good way of setting a callback for wp_unique_filename_callback, which would be the most beautiful way of doing this. So, instead we are usting the wp_handle_upload_prefilter to check for duplicates and rename the files...
	function handle_upload_prefilter($file) {

		wpro()->debug->log('WPRO::handle_upload_prefilter($file);');
		wpro()->debug->log('-> $file = ');
		wpro()->debug->log(print_r($file, true));

		$upload = wp_upload_dir();

		$name = $file['name'];
		$path = trim($upload['url'], '/') . '/' . $name;

		$counter = 0;
		while ($this->backend->file_exists($path)) {
			if (preg_match('/\.([^\.\/]+)$/', $file['name'], $regs)) {
				$ending = '.' . $regs[1];
				$preending = substr($file['name'], 0, 0 - strlen($ending));
				$name = $preending . '_' . $counter . $ending;
			} else {
				$name = $file['name'] . '_' . $counter;
			}
			$path = trim($upload['url'], '/') . '/' . $name;
			$counter++;
		}

		$file['name'] = $name;

		return $file;
	}


	function load_image_to_edit_path($filepath) {

		wpro()->debug->log('WPRO::load_image_to_edit_path("' . $filepath . '");');

		if (substr($filepath, 0, 7) == 'http://' || substr($filepath, 0, 8) == 'https://') {

			$ending = '';
			if (preg_match('/\.([^\.\/]+)$/', $filepath, $regs)) $ending = '.' . $regs[1];

			$tmpfile = $this->tempdir . 'wpro' . time() . rand(0, 999999) . $ending;
			while (file_exists($tmpfile)) $tmpfile = $this->tempdir . 'wpro' . time() . rand(0, 999999) . $ending;

			$filepath = $this->wpro()->url->normalize($filepath);

			wpro()->debug->log('-> Loading file from: ' . $filepath);
			wpro()->debug->log('-> Storing file at: ' . $tmpfile);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $filepath);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_AUTOREFERER, true);

			$fh = fopen($tmpfile, 'w');
			fwrite($fh, curl_exec_follow($ch));
			fclose($fh);

			$this->removeTemporaryLocalData($tmpfile);

			return $tmpfile;

		}
		return $filepath;
	}

	function load_image_to_local_path($filepath, $attachment_id) {

		wpro()->debug->log('WPRO::load_image_to_local_path("' . $filepath . '");');

		$fileurl = apply_filters( 'load_image_to_edit_attachmenturl', wp_get_attachment_url( $attachment_id ), $attachment_id, 'full' );

		if (substr($fileurl, 0, 7) == 'http://') {

			$fileurl = $this->wpro()->url->normalize($fileurl);

			wpro()->debug->log('-> Loading file from: ' . $fileurl);
			wpro()->debug->log('-> Storing file at: ' . $filepath);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $fileurl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_AUTOREFERER, true);

			$fh = fopen($filepath, 'w');
			fwrite($fh, curl_exec_follow($ch));
			fclose($fh);

			$this->removeTemporaryLocalData($filepath);

			return $filepath;

		}
		return $filepath;
	}

	function save_image_file($dummy, $filename, $image, $mime_type, $post_id) {

		wpro()->debug->log('WPRO::save_image_file("' . $filename . '", "' . $mime_type . '", "' . $post_id . '");');


		if (substr($filename, 0, strlen($this->tempdir)) != $this->tempdir) return false;
		$tmpfile = substr($filename, strlen($this->tempdir));
		if (!preg_match('/^wpro[0-9]+(\/.+)$/', $tmpfile, $regs)) return false;

		$tmpfile = $regs[1];

		wpro()->debug->log('-> Storing image as temporary file: ' . $filename);
		$image->save($filename, $mime_type);

		$upload = wp_upload_dir();
		$url = $upload['baseurl'];
		if (substr($url, -1) != '/') $url .= '/';
		while (substr($tmpfile, 0, 1) == '/') $tmpfile = substr($tmpfile, 1);
		$url .= $tmpfile;

		return $this->backend->upload($filename, $this->wpro()->url->normalize($url), $mime_type);

	}

	function update_attachment_metadata($data) {
		wpro()->debug->log('WPRO::update_attachment_metadata();');
		if (!is_array($data) || !isset($data['sizes']) || !is_array($data['sizes'])) return $data;
		$upload_dir = wp_upload_dir();
		$filepath = $upload_dir['basedir'] . '/' . preg_replace('/^(.+\/)?.+$/', '\\1', $data['file']);
		foreach ($data['sizes'] as $size => $sizedata) {
			$file = $filepath . $sizedata['file'];
			$url = $upload_dir['baseurl'] . substr($file, strlen($upload_dir['basedir']));
			$mime = 'application/octet-stream';
			switch(substr($file, -4)) {
			case '.gif':
				$mime = 'image/gif';
				break;
			case '.jpg':
				$mime = 'image/jpeg';
				break;
			case '.png':
				$mime = 'image/png';
				break;
			}

			$this->backend->upload($file, $url, $mime);
		}
		return $data;
	}

	function upload_bits($data) {

		wpro()->debug->log('WPRO::upload_bits($data);');
		wpro()->debug->log('-> $data = ');
		wpro()->debug->log(print_r($data, true));

		$ending = '';
		if (preg_match('/\.([^\.\/]+)$/', $data['name'], $regs)) $ending = '.' . $regs[1];

		$tmpfile = $this->tempdir . 'wpro' . time() . rand(0, 999999) . $ending;
		while (file_exists($tmpfile)) $tmpfile = $this->tempdir . 'wpro' . time() . rand(0, 999999) . $ending;

		$fh = fopen($tmpfile, 'wb');
		fwrite($fh, $data['bits']);
		fclose($fh);

		$upload = wp_upload_dir();

		return array(
			'file' => $tmpfile,
			'url' => $this->wpro()->url->normalize($upload['url'] . '/' . $data['name']),
			'error' => false
		);
	}


}
