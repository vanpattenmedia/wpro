<?php

class TmpDirTest extends WP_UnitTestCase {

	function testSystemTemporaryDirectoryShouldBeSomething() {
		$this->assertNotEmpty(wpro()->tmpdir->sysTmpDir());
	}

	function testSystemTemporaryDirectoryShouldNotHaveTailingSlash() {
		$this->assertStringEndsNotWith(wpro()->tmpdir->sysTmpDir(), '/');
	}

	function testRequestTmpDirToBeSubdirToTheSystemTmpDir() {
		$this->assertStringStartsWith(wpro()->tmpdir->sysTmpDir() . '/wpro', wpro()->tmpdir->reqTmpDir());
	}

	function testRequestTemporaryDirectoryShouldNotHaveTailingSlash() {
		$this->assertStringEndsNotWith(wpro()->tmpdir->reqTmpDir(), '/');
	}


}

