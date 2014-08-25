<?php

class DebugTest extends WP_UnitTestCase {

	function testDebugCache() {
		wpro_debug('This is a test.');
		$this->assertTrue(wpro_is_in_debug_cache('This is a test.'));
		wpro_clean_debug_cache();
		$this->assertFalse(wpro_is_in_debug_cache('This is a test.'));
		wpro_debug('This is a test.');
		wpro_debug('This is another test.');
		$this->assertTrue(wpro_is_in_debug_cache('This is a test.'));
		$this->assertTrue(wpro_is_in_debug_cache('This is another test.'));
		$this->assertFalse(wpro_is_in_debug_cache('This is a third test.'));
	}

}

