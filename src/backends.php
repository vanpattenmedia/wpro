<?php

if (!defined('ABSPATH')) exit();

class WPRO_Backends {

	private $instances;

	function __construct() {
		$this->instances = array();
	}

	function backend_names() {
		$result = [];
		foreach ($this->instances as $instance) {
			$result[] = $instance->name;
		}
		return $result;
	}

	function has_backend($name) {
		$names = $this->backend_names();
		return in_array($name, $names);
	}

	function register($instance_of_backend_class) {
		if ($this->has_backend($instance_of_backend_class->name)) return false;
		$this->instances[] = $instance_of_backend_class;
		return true;
	}

}

