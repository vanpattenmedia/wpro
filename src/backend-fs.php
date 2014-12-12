<?php

if (!defined('ABSPATH')) exit();

class WPRO_Backend_Filesystem {

	const NAME = 'Custom Filesystem Path';

	function activate() {
		wpro()->options->register('wpro-fs-path');

		add_filter('wpro_backend_handle_upload', array($this, 'handle_upload'));
		add_filter('wpro_backend_retrieval_baseurl', array($this, 'url'));
	}

	function admin_form() {
		?>
			<h3><?php echo(self::NAME); ?></h3>
			<p class="description">
				Use this backend for storage in a custom filesystem path,
				for example a shared network folder, or such.
			</p>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">Filesystem Path</th>
					<td>
						<input type="text" name="wpro-fs-path" />
					</td>
				</tr>
			</table>
		<?php
	}

	function handle_upload($data) {
		$file = $data['file'];
		$url = $data['url'];
		$mime = $data['type'];

		wpro()->debug->log('WPROS3::upload("' . $file . '", "' . $url . '", "' . $mime . '");');
		$url = $this->wpro()->url->normalize($url);
		if (!preg_match('/^http(s)?:\/\/([^\/]+)\/(.*)$/', $url, $regs)) return false;
		$url = $regs[3];

		if (!file_exists($file)) return false;
		$this->removeTemporaryLocalData($file);

		$fin = fopen($file, 'r');
		if (!$fin) return false;

		$fout = fsockopen($this->endpoint, 80, $errno, $errstr, 30);
		if (!$fout) return false;
		$datetime = gmdate('r');
		$string2sign = "PUT\n\n" . $mime . "\n" . $datetime . "\nx-amz-acl:public-read\n/" . $url;

		wpro()->debug->log('STRING TO SIGN:');
		wpro()->debug->log($string2sign);
		$debug = '';
		for ($i = 0; $i < strlen($string2sign); $i++) $debug .= dechex(ord(substr($string2sign, $i, 1))) . ' ';
		wpro()->debug->log($debug);

		// Todo: Make this work with php cURL instead of fsockopen/etc..

		$query = "PUT /" . $url . " HTTP/1.1\n";
		$query .= "Host: " . $this->endpoint . "\n";
		$query .= "x-amz-acl: public-read\n";
		$query .= "Connection: keep-alive\n";
		$query .= "Content-Type: " . $mime . "\n";
		$query .= "Content-Length: " . filesize($file) . "\n";
		$query .= "Date: " . $datetime . "\n";
		$query .= "Authorization: AWS " . $this->key . ":" . $this->amazon_hmac($string2sign) . "\n\n";

		wpro()->debug->log('SEND:');
		wpro()->debug->log($query);

		fwrite($fout, $query);
		while (feof($fin) === false) fwrite($fout, fread($fin, 8192));
		fclose($fin);

		// Get the amazon response:
		wpro()->debug->log('RECEIVE:');
		$response = '';
		while (!feof($fout)) {
			$data = fgets($fout, 256);
			wpro()->debug->log($data);
			$response .= $data;
			if (strpos($response, "\r\n\r\n") !== false) { // Header fully returned.
				wpro()->debug->log('ALL RESPONSE HEADERS RECEIVED.');
				if (strpos($response, 'Content-Length: 0') !== false) break; // Return if Content-Length: 0 (and header is fully returned)
				if (substr($response, -7) == "\r\n0\r\n\r\n") break; // Keep-alive responses does not return EOF, they end with this string.
			}
		}

		fclose($fout);

		if (strpos($response, '<Error>') !== false) return false;

		return true;
	}

	function deactivate() {
		wpro()->options->deregister('wpro-fs-path');

		remove_filter('wpro_backend_handle_upload', array($this, 'handle_upload'));
		remove_filter('wpro_backend_retrieval_baseurl', array($this, 'url'));
	}

	function url($value) {
		$url = admin_url('admin-ajax.php?action=wpro&file=');
		return $url;
	}
		

}

function wpro_setup_fs_backend() {
	wpro()->backends->register('WPRO_Backend_Filesystem'); // Name of the class.
}
add_action('wpro_setup_backend', 'wpro_setup_fs_backend');

