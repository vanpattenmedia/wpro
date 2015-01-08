<?php

if (!defined('ABSPATH')) exit();

class WPRO_Uploads {

	function __construct() {
		$log = wpro()->debug->logblock('WPRO_Uploads::__construct()');

		add_filter('wp_handle_upload', array($this, 'handle_upload'));
		add_filter('wp_generate_attachment_metadata', array($this, 'generate_attachment_metadata')); // We use this filter to store resized versions of the images.
		add_filter('wp_update_attachment_metadata', array($this, 'update_attachment_metadata')); // We use this filter to store resized versions of the images.
		add_filter('load_image_to_edit_path', array($this, 'load_image_to_edit_path')); // This filter downloads the image to our local temporary directory, prior to editing the image.
		add_filter('get_attached_file', array($this, 'load_image_to_local_path'), 10, 2); // This filter downloads the image to our local temporary directory, prior to using the image.
		add_filter('wp_save_image_file', array($this, 'save_image_file')); // Store image file.
		add_filter('wp_save_image_editor_file', array($this, 'save_image_file'), 10, 5);
		add_filter('wp_upload_bits', array($this, 'upload_bits')); // On XMLRPC uploads and image editor edits, files arrives as strings which we are handling in this filter.
		add_filter('wp_handle_upload_prefilter', array($this, 'handle_upload_prefilter')); // This is where we check for filename dupes (and change them to avoid overwrites).

		return $log->logreturn(true);
	}

	function exists($path) {
		$log = wpro()->debug->logblock('WPRO_Uploads::exists()');

		$path = $this->wpro()->url->normalize($path);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_NOBODY, 1);
		curl_setopt($ch, CURLOPT_URL, $path);
		$result = trim(curl_exec_follow($ch));

		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($httpCode != 200) return $log->logreturn(false);

		return $log->logreturn(true);
	}

	function generate_attachment_metadata($data) {
		$log = wpro()->debug->logblock('WPRO_Uploads::generate_attachment_metadata()');

		if (wpro()->backends->is_backend_activated()) {

			if (!is_array($data) || !isset($data['sizes']) || !is_array($data['sizes'])) return $log->logreturn($data);

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

		}

		return $log->logreturn($data);
	}

	function handle_upload($data) {
		$log = wpro()->debug->logblock('WPRO_Uploads::handle_upload()');

		if (wpro()->backends->is_backend_activated()) {

			$data['url'] = wpro()->url->normalize($data['url']);
			if (!file_exists($data['file'])) return false; //TODO: Test what is happening in this situation.

			$response = wpro()->backends->active_backend()->upload($data['file'], $data['url'], $data['type']);
			$data = apply_filters('wpro_backend_handle_upload', $data);

			// One thing has changed here. Previously, we returned false from this function, when upload failed.
			// TODO: Check what is happening here on failing uploads.

		}

		return $log->logreturn($data);
	}

	// Handle duplicate filenames:
	// Wordpress never calls the wp_handle_upload_overrides filter properly, so we do not have any good way of setting a callback for wp_unique_filename_callback, which would be the most beautiful way of doing this. So, instead we are usting the wp_handle_upload_prefilter to check for duplicates and rename the files...
	function handle_upload_prefilter($file) {
		$log = wpro()->debug->logblock('WPRO_Uploads::handle_upload_prefilter()');

		if (wpro()->backends->is_backend_activated()) {

			$upload = wp_upload_dir();

			$name = $file['name'];
			$path = trim($upload['url'], '/') . '/' . $name;

			$counter = 0;
			while (wpro()->backends->active_backend->file_exists($path)) {
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

		}

		return $log->logreturn($file);
	}


	function load_image_to_edit_path($filepath) {
		$log = wpro()->debug->logblock('WPRO_Uploads::load_image_to_edit_path()');

		if (substr($filepath, 0, 7) == 'http://' || substr($filepath, 0, 8) == 'https://') {

			$ending = '';
			if (preg_match('/\.([^\.\/]+)$/', $filepath, $regs)) $ending = '.' . $regs[1];

			$tmpfile = $this->tempdir . 'wpro' . time() . rand(0, 999999) . $ending;
			while (file_exists($tmpfile)) $tmpfile = $this->tempdir . 'wpro' . time() . rand(0, 999999) . $ending;

			$filepath = $this->wpro()->url->normalize($filepath);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $filepath);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_AUTOREFERER, true);

			$fh = fopen($tmpfile, 'w');
			fwrite($fh, curl_exec_follow($ch));
			fclose($fh);

			$this->removeTemporaryLocalData($tmpfile);

			return $log->logreturn($tmpfile);

		}
		return $log->logreturn($filepath);
	}

	function load_image_to_local_path($filepath, $attachment_id) {
		$log = wpro()->debug->logblock('WPRO_Uploads::load_image_to_local_path($filepath = "' . $filepath . '", $attachment_id = ' . $attachment_id . ')');

		if (file_exists ($filepath)) {

			// When no backend is active:
			// Without this file_exists, during an upload to WordPress,
			// it will try to download the image to it's own path,
			// which results in the upload being 0 bytes in length.

			$log->log("Don't download. File already exists.");

		} else {

			$attachment_url = wp_get_attachment_url( $attachment_id );
			$log->log('$attachment_url = "' . $attachment_url . '"');
			$fileurl = apply_filters( 'load_image_to_edit_attachmenturl', $attachment_url, $attachment_id, 'full' );
			$log->log('$fileurl = "' . $fileurl . '"');

			if (substr($fileurl, 0, 7) == 'http://') {

				$fileurl = wpro()->url->normalize($fileurl);

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $fileurl);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_AUTOREFERER, true);

				$fh = fopen($filepath, 'w');
				fwrite($fh, curl_exec_follow($ch));
				fclose($fh);

				//$this->removeTemporaryLocalData($filepath);

				return $log->logreturn($filepath);

			}

		}

		return $log->logreturn($filepath);
	}

	function save_image_file($dummy, $filename, $image, $mime_type, $post_id) {
		$log = wpro()->debug->logblock('WPRO_Uploads::save_image_file($dummy = "' . $dummy . '", $filename = "' . $filename . '", $image, $mime_type = "' . $mime_type . '", $post_id = ' . $post_id .')');

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

		return $log->logreturn($this->backend->upload($filename, $this->wpro()->url->normalize($url), $mime_type));

	}

	function update_attachment_metadata($data) {
		$log = wpro()->debug->logblock('WPRO_Uploads::update_attachment_metadata()');

		if (wpro()->backends->is_backend_activated()) {

			if (!is_array($data) || !isset($data['sizes']) || !is_array($data['sizes'])) return $log->logreturn($data);
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
		}

		return $log->logreturn($data);
	}

	function upload_bits($data) {
		$log = wpro()->debug->logblock('WPRO_Uploads::upload_bits()');
		if (!wpro()->backends->is_backend_activated()) {
			$log->log('There is no backend.');
			$log->logblockend();
			return $data;
		}

		$ending = '';
		if (preg_match('/\.([^\.\/]+)$/', $data['name'], $regs)) $ending = '.' . $regs[1];

		$tmpfile = $this->tempdir . 'wpro' . time() . rand(0, 999999) . $ending;
		while (file_exists($tmpfile)) $tmpfile = $this->tempdir . 'wpro' . time() . rand(0, 999999) . $ending;

		$fh = fopen($tmpfile, 'wb');
		fwrite($fh, $data['bits']);
		fclose($fh);

		$upload = wp_upload_dir();

		return $log->logreturn(array(
			'file' => $tmpfile,
			'url' => $this->wpro()->url->normalize($upload['url'] . '/' . $data['name']),
			'error' => false
		));
	}


}
