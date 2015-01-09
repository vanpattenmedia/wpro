<?php

if (!defined('ABSPATH')) exit();

class WPRO_Backend_Filesystem {

	const NAME = 'Custom Filesystem Path';

	function activate() {
		$log = wpro()->debug->logblock('WPRO_Backend_Filesystem::activate()');

		wpro()->options->register('wpro-fs-path');

		add_filter('wpro_backend_handle_upload', array($this, 'handle_upload'));
		add_filter('wpro_backend_retrieval_baseurl', array($this, 'url'));

		return $log->logreturn(true);
	}

	function admin_form() {
		$log = wpro()->debug->logblock('WPRO_Backend_Filesystem::admin_form()');
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
						<input type="text" name="wpro-fs-path" value="<?php echo(wpro()->options->get_option('wpro-fs-path')); ?>" />
					</td>
				</tr>
			</table>
		<?php
		return $log->logreturn(true);
	}

	function handle_upload($data) {
		$log = wpro()->debug->logblock('WPRO_Backend_Filesystem::handle_upload()');

		$file = $data['file'];
		$url = $data['url'];
		$mime = $data['type'];

		wpro()->debug->logblock('WPROS3::upload("' . $file . '", "' . $url . '", "' . $mime . '");');
		$url = wpro()->url->normalize($url);
		if (!preg_match('/^http(s)?:\/\/([^\/]+)\/(.*)$/', $url, $regs)) return false;
		$url = $regs[3];

		if (!file_exists($file)) return $log->logreturn(false);

		$path = rtrim(wpro()->options->get('wpro-fs-path'), '/') . '/' . trim($url, '/');

		if (!is_dir(dirname($path))) mkdir(dirname($path), 0777, true);
		if (!is_dir(dirname($path))) return $log->logreturn(false);

		return rename($file, $path);

		return $log->logreturn(true);
	}

	function deactivate() {
		$log = wpro()->debug->logblock('WPRO_Backend_Filesystem::deactivate()');

		wpro()->options->deregister('wpro-fs-path');

		remove_filter('wpro_backend_handle_upload', array($this, 'handle_upload'));
		remove_filter('wpro_backend_retrieval_baseurl', array($this, 'url'));

		return $log->logreturn(true);
	}

	function url($value) {
		$log = wpro()->debug->logblock('WPRO_Backend_Filesystem::url()');

		$url = admin_url('admin-ajax.php?action=wpro&file=');

		return $log->logreturn($url);
	}
		

}

function wpro_setup_fs_backend() {
	wpro()->backends->register('WPRO_Backend_Filesystem'); // Name of the class.
}
add_action('wpro_setup_backend', 'wpro_setup_fs_backend');

