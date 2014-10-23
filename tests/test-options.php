<?php

class OptionsTest extends WP_UnitTestCase {

	function testAvailableOptions() {
		$this->assertTrue(wpro()->options->is_an_option('wpro-service'));
		$this->assertTrue(wpro()->options->is_an_option('wpro-folder'));
		$this->assertTrue(wpro()->options->is_an_option('wpro-tempdir'));
		$this->assertTrue(wpro()->options->is_an_option('wpro-aws-key'));
		$this->assertTrue(wpro()->options->is_an_option('wpro-aws-secret'));
		$this->assertTrue(wpro()->options->is_an_option('wpro-aws-bucket'));
		$this->assertTrue(wpro()->options->is_an_option('wpro-aws-cloudfront'));
		$this->assertTrue(wpro()->options->is_an_option('wpro-aws-virthost'));
		$this->assertTrue(wpro()->options->is_an_option('wpro-aws-endpoint'));
		$this->assertTrue(wpro()->options->is_an_option('wpro-aws-ssl'));
		$this->assertTrue(wpro()->options->is_an_option('wpro-ftp-server'));
		$this->assertTrue(wpro()->options->is_an_option('wpro-ftp-user'));
		$this->assertTrue(wpro()->options->is_an_option('wpro-ftp-password'));
		$this->assertTrue(wpro()->options->is_an_option('wpro-ftp-pasvmode'));
		$this->assertTrue(wpro()->options->is_an_option('wpro-ftp-webroot'));

		$this->assertFalse(wpro()->options->is_an_option('wpro-some-bullshit'));
	}


	function testRegisterOption() {
		$this->assertFalse(wpro()->options->is_an_option('unit-test-option'));
		wpro()->options->register('unit-test-option');
		$this->assertTrue(wpro()->options->is_an_option('unit-test-option'));
	}
}

