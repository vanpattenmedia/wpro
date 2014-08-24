<?php

class TmpDirTest extends WP_UnitTestCase {

	function testSystemTemporaryDirectoryShouldBeSomething() {
		$this->assertNotEmpty(wpro_sysTmpDir());
	}

	function testSystemTemporaryDirectoryShouldNotHaveTailingSlash() {
		$this->assertStringEndsNotWith(wpro_sysTmpDir(), '/');
	}

	function testRequestTmpDirToBeSubdirToTheSystemTmpDir() {
		$this->assertStringStartsWith(wpro_sysTmpDir() . '/wpro', wpro_reqTmpDir());
	}

	function testRequestTemporaryDirectoryShouldNotHaveTailingSlash() {
		$this->assertStringEndsNotWith(wpro_reqTmpDir(), '/');
	}


}

