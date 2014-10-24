<?php

if (!defined('ABSPATH')) exit();

class WPRO_Url {

	// URL encode (i.e. convert to %xx etc in URLs).
	function normalize($url) {
		if (strpos($url, '%') !== false) return $url;
		$url = explode('/', $url);
		foreach ($url as $key => $val) $url[$key] = urlencode($val);
		return str_replace('%3A', ':', join('/', $url));
	}

	function upload_dir($data) {

		$backend = wpro()->backends->active_backend();
		if (is_null($backend)) return $data;

		$baseurl = apply_filters('wpro_backend_retrieval_baseurl', $data['baseurl']);

		return (array(
			'path' => wpro()->tmpdir->reqTmpDir() . $data['subdir'],
			'url' => $baseurl . $data['subdir'],
			'subdir' => $data['subdir'],
			'basedir' => wpro()->tmpdir->reqTmpDir(),
			'baseurl' => $baseurl,
			'error' => ''
		));

	}

}
