<?php

if (!defined('ABSPATH')) exit();

class WPRO_Backends {

	public $active_backend;
	private $backends;

	function __construct() {
		$this->active_backend = null;
		$this->backends = array();
	}

	function activate_backend($name) {
		$this->deactivate_backend();
		$this->active_backend = $this->backend_by_name($name);
		if (is_null($this->active_backend)) {
			wpro()->options->set('wpro-service', '');
			return false;
		}
		if (method_exists($this->active_backend, 'activate')) {
			$this->active_backend->activate();
		}
		$active_backend = $this->active_backend;
		wpro()->options->set('wpro-service', $active_backend::NAME);
		return true;
	}

	function backend_by_name($name) {
		foreach ($this->backends as $key => $val) {
			if ($key == $name) return $val;
		}
		return null;
	}

	function backend_names() {
		$result = array_keys($this->backends);
		sort($result);
		return $result;
	}
	
	function deactivate_backend() {
		if (!is_null($this->active_backend)) {
			if (method_exists($this->active_backend, 'deactivate')) {
				$this->active_backend->deactivate();
			}
			wpro()->options->set('wpro-service', '');
			$this->active_backend = null;
		}
	}

	function has_backend($name) {
		$names = $this->backend_names();
		return in_array($name, $names);
	}

	function register($backend_class_name) {
		if ($this->has_backend($backend_class_name::NAME)) return false;
		$this->backends[$backend_class_name::NAME] = new $backend_class_name();
		return true;
	}

}

