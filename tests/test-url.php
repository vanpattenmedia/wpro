<?php

class UrlTest extends WP_UnitTestCase {

	function testUrlNormalizerShouldMakeUrlEncoding() {
		$this->assertEquals(wpro()->url->normalize('http://www.example.org/Alfred Testar.jpg'), 'http://www.example.org/Alfred+Testar.jpg');
		$this->assertEquals(wpro()->url->normalize('http://www.example.org/Alfred Godoy Är Ball.jpg'), 'http://www.example.org/Alfred+Godoy+%C3%84r+Ball.jpg');
	}

}

