<?php

class BackendS3Test extends WP_UnitTestCase {

	function testFiltersAreRegistered() {
		$s3_backend = wpro()->backends->backend_by_name('Amazon S3');

		// 10 is the filter priority:
		$this->assertEquals(has_filter('wpro_backend_retrieval_baseurl', array($s3_backend, 'url')), 10);
	}
}

