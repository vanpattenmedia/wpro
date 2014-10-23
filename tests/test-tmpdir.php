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

	function testRequestTemporaryDirectoryShouldBeSameEachTimeWithinTheSameRequest() {
		$this->assertEquals(wpro()->tmpdir->reqTmpDir(), wpro()->tmpdir->reqTmpDir());
		$this->assertEquals(wpro()->tmpdir->reqTmpDir(), wpro()->tmpdir->reqTmpDir());
		$this->assertEquals(wpro()->tmpdir->reqTmpDir(), wpro()->tmpdir->reqTmpDir());
	}

	function testCleanUpShouldRemoveTemporaryDirectoryRecursively() {
		mkdir(wpro()->tmpdir->reqTmpDir() . '/alfred/was', 0777, true);
		touch(wpro()->tmpdir->reqTmpDir() . '/alfred/was/here');
		$this->assertTrue(file_exists(wpro()->tmpdir->reqTmpDir()));
		wpro()->tmpdir->cleanUp();
		$this->assertFalse(file_exists(wpro()->tmpdir->reqTmpDir()));
	}

}

