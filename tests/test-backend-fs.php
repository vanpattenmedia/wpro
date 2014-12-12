<?php

class BackendFSTest extends WP_UnitTestCase {

	function testFSOptions() {
		wpro()->backends->activate_backend('Custom Filesystem Path');
		$this->assertTrue(wpro()->options->is_an_option('wpro-fs-path'));
		wpro()->backends->deactivate_backend();
		$this->assertFalse(wpro()->options->is_an_option('wpro-fs-path'));
	}

	function testDirsFilterFunction() {

		wpro()->backends->activate_backend('Custom Filesystem Path');

		$dirs = wp_upload_dir();
		$this->assertEquals($dirs['baseurl'], admin_url('admin-ajax.php?action=wpro&file='));

		wpro()->backends->deactivate_backend();

	}

	function testUploadHandleFunction() {
		// Create a 1x1 pixel transparent PNG:
		$tmpfname = tempnam('/tmp', 'WPROTEST');
		$fh = fopen($tmpfname, 'w');
		$transparentpng = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII=';
		fwrite($fh, base64_decode($transparentpng));
		fclose($fh);

		$tmpdir = tempnam('/tmp', 'WPROTESTFSPATH');
		unlink($tmpdir);
		mkdir($tmpdir);
		wpro()->tmpdir->cleanUpDirs[] = $tmpdir;

		wpro()->backends->activate_backend('Custom Filesystem Path');
		wpro()->options->set('wpro-fs-path', $tmpdir);
		wpro()->backends->active_backend->handle_upload(array(
			'file' => $tmpfname,
			'url' => 'http://www.example.org/wp-content/uploads/2014/05/test.png',
			'type' => 'image/png'
		));

		wpro()->backends->deactivate_backend();

		$this->assertEquals(base64_encode(file_get_contents($tmpdir . '/wp-content/uploads/2014/05/test.png')), $transparentpng);

	}

}

