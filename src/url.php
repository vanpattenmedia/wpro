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


}
