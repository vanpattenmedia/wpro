<?php

class BackendFSTest extends WP_UnitTestCase {

	function testFSOptions() {
		wpro()->backends->activate_backend('Custom Filesystem Path');
		$this->assertTrue(wpro()->options->is_an_option('wpro-fs-path'));
		wpro()->backends->deactivate_backend();
		$this->assertFalse(wpro()->options->is_an_option('wpro-fs-path'));
	}

	function testDirsFilterFunctionForFSBackend() {

		wpro()->backends->activate_backend('Custom Filesystem Path');

		$dirs = wp_upload_dir();
		$this->assertEquals($dirs['baseurl'], admin_url('admin-ajax.php?action=wpro&file='));

		wpro()->backends->deactivate_backend();

	}

}

