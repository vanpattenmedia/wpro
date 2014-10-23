<?php

class DebugTest extends WP_UnitTestCase {

	function testDebugCache() {
		wpro()->debug->log('This is a test.');
		$this->assertTrue(wpro()->debug->is_in_cache('This is a test.'));
		wpro()->debug->clean_debug_cache();
		$this->assertFalse(wpro()->debug->is_in_cache('This is a test.'));
		wpro()->debug->log('This is a test.');
		wpro()->debug->log('This is another test.');
		$this->assertTrue(wpro()->debug->is_in_cache('This is a test.'));
		$this->assertTrue(wpro()->debug->is_in_cache('This is another test.'));
		$this->assertFalse(wpro()->debug->is_in_cache('This is a third test.'));
	}

}

