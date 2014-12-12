<?php

class OptionsTest extends WP_UnitTestCase {

	function testAvailableOptions() {
		$this->assertTrue(wpro()->options->is_an_option('wpro-service'));
		$this->assertTrue(wpro()->options->is_an_option('wpro-folder'));
		$this->assertTrue(wpro()->options->is_an_option('wpro-tempdir'));

		$this->assertFalse(wpro()->options->is_an_option('wpro-some-bullshit'));
	}


	function testRegisterOption() {
		$this->assertFalse(wpro()->options->is_an_option('unit-test-option'));
		wpro()->options->register('unit-test-option');
		$this->assertTrue(wpro()->options->is_an_option('unit-test-option'));
	}
}

