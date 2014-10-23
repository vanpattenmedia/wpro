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

		$data['basedir'] = wpro()->tmpdir->reqTmpDir();


/*

WPRO::upload_dir($data);
-> $data =
Array
(
    [path] => /home/qwerty/dev/hotrest/htdocs/wp-content/uploads/2014/10
    [url] => http://hotrest.dev/wp-content/uploads/2014/10
    [subdir] => /2014/10
    [basedir] => /home/qwerty/dev/hotrest/htdocs/wp-content/uploads
    [baseurl] => http://hotrest.dev/wp-content/uploads
    [error] => 
)
WPROGeneric::removeTemporaryLocalData("/tmp/wpro1414088049539454/2014/10");
-> RETURNS =
Array
(
    [path] => /tmp/wpro1414088049539454/2014/10
    [url] => http://s3-eu-west-1.amazonaws.com/hrftest/2014/10
    [subdir] => /2014/10
    [basedir] => /tmp/wpro1414088049539454
    [baseurl] => http://s3-eu-west-1.amazonaws.com/hrftest
    [error] => 
)


*/
		$protocol = apply_filters('wpro_backend_retrieval_protocol', 'http');


		switch (wpro()->options->get('wpro-service')) {
		case 'ftp':
			$data['baseurl'] = 'http://' . trim(str_replace('//', '/', trim(wpro()->options->get('wpro-ftp-webroot'), '/') . '/' . trim(wpro()->options->get('wpro-folder'))), '/');
			break;
		default:
			if (wpro()->options->get('wpro-aws-cloudfront')) {
#				$data['baseurl'] = $protocol . '://' . trim(str_replace('//', '/', wpro()->options->get('wpro-aws-cloudfront') . '/' . trim(wpro()->options->get('wpro-folder'))), '/');
				$data['baseurl'] = 'CLOUDFRONT YADA YADA ';
			} elseif (wpro()->options->get('wpro-aws-virthost')) {
#				$data['baseurl'] = $protocol . '://' . trim(str_replace('//', '/', wpro()->options->get('wpro-aws-bucket') . '/' . trim(wpro()->options->get('wpro-folder'))), '/');
				$data['baseurl'] = 'VIRTUAL HOST YADA YADA';
			} else {



				# this needs some more testing, but it seems like we have to use the
			    # virtual-hosted-style for US Standard region, and the path-style
				# for region-specific endpoints:
				# (however we used the virtual-hosted style for everything before,
				# and that did work, so something has changed at amazons end.
				# is there any difference between old and new buckets?)
				if (wpro()->options->get('wpro-aws-endpoint') == 's3.amazonaws.com') {
					$data['baseurl'] = $protocol . '://' . trim(str_replace('//', '/', wpro()->options->get('wpro-aws-bucket') . '.s3.amazonaws.com/' . trim(wpro()->options->get('wpro-folder'))), '/');
				} else {
					$data['baseurl'] = $protocol . '://' . trim(str_replace('//', '/', wpro()->options->get('wpro-aws-endpoint') . '/' . wpro()->options->get('wpro-aws-bucket') . '/' . trim(wpro()->options->get('wpro-folder'))), '/');
				}



			}
		}
		$data['path'] = wpro()->tmpdir->reqTmpDir() . $data['subdir'];
		$data['url'] = $data['baseurl'] . $data['subdir'];

		wpro()->debug->log('-> RETURNS = ');
		wpro()->debug->log(print_r($data, true));

		return $data;
	}


}
