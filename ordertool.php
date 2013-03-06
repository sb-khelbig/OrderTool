<?php
class OrderToolError extends Exception {
	
}

class OrderTool {
	private $default_page;
	private $module_prefix;
	private $sub_page_prefix;
	
	public function __construct($default_page = '', $module_prefix = 'ot', $sub_page_prefix = 'sub') {
		$this->default_page = $default_page;
		$this->module_prefix = $module_prefix;
		$this->sub_page_prefix = $sub_page_prefix;
	}
	
	public function get_default_page() {
		return $this->default_page;
	}
	
	public function get_module_prefix() {
		return $this->module_prefix;
	}
	
	public function get_sub_page_prefix() {
		return $this->sub_page_prefix;
	}
	
	private function get_module(&$get) {
		$module_prefix = $this->get_module_prefix();
		if (isset($get[$module_prefix])) {
			$module = $get[$module_prefix];
			if (is_dir($module)) {
				return $module;
			} else {
				throw new OrderToolError('Module does not exist!');
			}
		} else {
			return False;	
		}
	}
	
	private function verify_include_string($string) {
		if (is_array($string)) {
			$include = join('', $string);
		} else {
			$include = $string;
		}
		$include .= '.php';
		if (file_exists($include)) {
			return $include;
		} else {
			throw new OrderToolError('Page not found!');
		}
	}
	
	public function get_include_string(&$info, &$get, &$post) {
		if ($module = $this->get_module($get)) {
			$include_string = array($module, '/', 'ot', '_', $module, '_');
			
			if (isset($get['sub'])) {
				$include_string = array_merge($include_string, array($get['sub'], '_'));
			}
			
			$id = (isset($get['id'])) ? $get['id'] : False;
			$action = (isset($post['action'])) ? $post['action'] : False;
			
			switch ($info['REQUEST_METHOD']) {
				case 'POST':
					$include_string[] = 'post';
					if ($action) {
						if ($id) {
							return $this->verify_include_string(array_merge($include_string, array('_', 'entity', '_', $action)));
						} else {
							return $this->verify_include_string(array_merge($include_string, array('_', $action)));
						}
					} else {
						throw new OrderToolError('POST-Action not set!');
					}
					break;
				case 'GET':
					$include_string[] = 'get';
					if ($id) {
						return $this->verify_include_string(array_merge($include_string, array('_', 'entity')));
					} else {
						return $this->verify_include_string(array_merge($include_string, array('_', 'overview')));
					}
					break;
				default:
					throw new OrderToolError('Method not supported!');
			}
		} else {
			$default = $this->get_default_page();
			return $this->verify_include_string($default);
		}
	}
	
	public function get_link($modul, $id = 0, $sub = '', $params = array()) {
		$values = array('p' => 'ordertool', $this->get_module_prefix() => $modul);
		if ($sub) {
			$values['sub'] = $sub;
		}
		if ($id) {
			$values['id'] = $id;
		}
		foreach ($params as $name => $value) {
			$values[$name] = $value;
		}
		$link = array();
		foreach ($values as $name => $value) {
			$link[] = "$name=$value";
		}
		return '?' . join('&', $link);
	}
}

$ot = new OrderTool();

include 'db/tables.php';
include 'functions/html.php';
//include 'db/ot_db_logging.php';

try {
	include $ot->get_include_string($_SERVER, $_GET, $_POST);
} catch (OrderToolError $e) {
	header("HTTP/1.0 404 Not Found");
	echo $e->getMessage();
}

