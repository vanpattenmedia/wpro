<?php

class OptionsTest extends WP_UnitTestCase {

	function testAvailableOptions() {
		$this->assertTrue(wpro_is_an_option('wpro-service'));
		$this->assertTrue(wpro_is_an_option('wpro-folder'));
		$this->assertTrue(wpro_is_an_option('wpro-tempdir'));
		$this->assertTrue(wpro_is_an_option('wpro-aws-key'));
		$this->assertTrue(wpro_is_an_option('wpro-aws-secret'));
		$this->assertTrue(wpro_is_an_option('wpro-aws-bucket'));
		$this->assertTrue(wpro_is_an_option('wpro-aws-cloudfront'));
		$this->assertTrue(wpro_is_an_option('wpro-aws-virthost'));
		$this->assertTrue(wpro_is_an_option('wpro-aws-endpoint'));
		$this->assertTrue(wpro_is_an_option('wpro-aws-ssl'));
		$this->assertTrue(wpro_is_an_option('wpro-ftp-server'));
		$this->assertTrue(wpro_is_an_option('wpro-ftp-user'));
		$this->assertTrue(wpro_is_an_option('wpro-ftp-password'));
		$this->assertTrue(wpro_is_an_option('wpro-ftp-pasvmode'));
		$this->assertTrue(wpro_is_an_option('wpro-ftp-webroot'));

		$this->assertFalse(wpro_is_an_option('wpro-some-bullshit'));
	}

}

