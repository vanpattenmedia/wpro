<?php

class BackendS3Test extends WP_UnitTestCase {

	function testRetrievalProtocolShouldBeHttpByDefault() {
		$s3_backend = wpro()->backends->backend_by_name('Amazon S3');
		wpro()->options->set('wpro-aws-ssl', '');
		$this->assertEquals($s3_backend->retrieval_protocol('-'), 'http');
	}
}

