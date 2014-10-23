<?php

class ExampleBackendClass {

	public $name;

	function __construct($name) {
		$this->name = $name;
	}

}

class BackendTest extends WP_UnitTestCase {

	function testRegisteringBackendClass() {
		$backend = new ExampleBackendClass('Unit Test Backend');
		$this->assertTrue(wpro()->backends->register($backend));
		$this->assertTrue(wpro()->backends->has_backend('Unit Test Backend'));
		$this->assertFalse(wpro()->backends->has_backend('Unit Test Backend Which Does not Exist'));

		// Registering the instance once again. Should return false.
		$this->assertFalse(wpro()->backends->register($backend));

		// Regostering another instance with the same name. Should not work. Names must be unique.
		$backend2 = new ExampleBackendClass('Unit Test Backend');
		$this->assertFalse(wpro()->backends->register($backend));

		// Regostering yet another instance with another name. Should work.
		$backend3 = new ExampleBackendClass('3rd Unit Test Backend');
		$this->assertTrue(wpro()->backends->register($backend3));
	}

}
