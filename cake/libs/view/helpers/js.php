<?php
/**
 * Javascript Generator class file.
 *
 * PHP Version 5.x
 *
 * CakePHP : Rapid  Development Framework (http://www.cakephp.org)
 * Copyright 2006-2009, Cake Software Foundation, Inc.
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright     Copyright 2006-2009, Cake Software Foundation, Inc.
 * @link          http://cakephp.org CakePHP Project
 * @package       cake
 * @subpackage    cake.cake.libs.view.helpers
 * @since         CakePHP v 1.2
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */

/**
 * Javascript Generator helper class for easy use of JavaScript.
 *
 * JsHelper provides an abstract interface for authoring JavaScript with a
 * given client-side library.
 *
 * @package       cake
 * @subpackage    cake.cake.libs.view.helpers
 */
class JsHelper extends Object {
	private $base = null;
	private $webroot = null;
	private $here = null;
	private $params = null;
	private $action = null;
	private $data = null;
	private $themeWeb = null;
	private $plugin = null;

	private $helpers = array();

	private $hook = null;

	private $__objects = array();

	private $effectMap = array(
		'Appear', 'Fade', 'Puff', 'BlindDown', 'BlindUp', 'SwitchOff', 'SlideDown', 'SlideUp',
		'DropOut', 'Shake', 'Pulsate', 'Squish', 'Fold', 'Grow', 'Shrink', 'Highlight', 'toggle'
	);

	private $output = false;
/**
 * 
 */
	public function __construct() {
		$this->effectMap = array_combine(
			array_map('strtolower', $this->effectMap),
			$this->effectMap
		);
		parent::__construct();
	}

	public function call__($method, $params) {
		if (is_object($this->hook) && method_exists($this->hook, $method)) {
			$this->hook->dispatchMethod($method . '_', $params);
		}
		if (method_exists($this, $method . '_')) {
			return $this->dispatchMethod($method . '_', $params);
		}
	}

	public function alert_($message) {
		return 'alert("' . $this->escape($message) . '");';
	}

	public function if_($if, $then, $else = null, $elseIf = array()) {
		$len = strlen($if) - 1;
		if ($if{$len} == ';') {
			$if{$len} = null;
		}

		$out = 'if (' . $if . ') { ' . $then . ' }';

		foreach ($elseIf as $cond => $exec) {
			//$out .=
		}

		if (!empty($else)) {
			$out .= ' else { ' . $else . ' }';
		}

		return $out;
	}

	public function confirm_($message) {
		return 'confirm("' . $this->escape($message) . '");';
	}

	public function prompt_($message, $default = '') {
		return 'prompt("' . $this->escape($message) . '", "' . $this->escape($default) . '");';
	}

/*
 * Tries a series of expressions, and executes after first successful completion.
 * (See Prototype's Try.these).
 *
 * @return string
 */
	public function tryThese_($expr1, $expr2, $expr3) {
	}

/**
 * Loads a remote URL
 *
 * @param  string $url
 * @param  array  $options
 * @return string
 */
	public function load_($url = null, $options = array()) {

		if (isset($options['update'])) {
			if (!is_array($options['update'])) {
				$func = "new Ajax.Updater('{$options['update']}',";
			} else {
				$func = "new Ajax.Updater(document.createElement('div'),";
			}
			if (!isset($options['requestHeaders'])) {
				$options['requestHeaders'] = array();
			}
			if (is_array($options['update'])) {
				$options['update'] = join(' ', $options['update']);
			}
			$options['requestHeaders']['X-Update'] = $options['update'];
		} else {
			$func = "new Ajax.Request(";
		}

		$func .= "'" . Router::url($url) . "'";
		$ajax = new AjaxHelper();
		$func .= ", " . $ajax->__optionsForAjax($options) . ")";

		if (isset($options['before'])) {
			$func = "{$options['before']}; $func";
		}
		if (isset($options['after'])) {
			$func = "$func; {$options['after']};";
		}
		if (isset($options['condition'])) {
			$func = "if ({$options['condition']}) { $func; }";
		}
		if (isset($options['confirm'])) {
			$func = "if (confirm('" . $this->Javascript->escapeString($options['confirm'])
				. "')) { $func; } else { return false; }";
		}
		return $func;
	}

/**
 * Redirects to a URL
 *
 * @param  mixed $url
 * @param  array  $options
 * @return string
 */
	public function redirect_($url = null) {
		return 'window.location = "' . Router::url($url) . '";';
	}

/**
 * Escape a string to be JavaScript friendly.
 *
 * List of escaped ellements:
 *	+ "\r\n" => '\n'
 *	+ "\r" => '\n'
 *	+ "\n" => '\n'
 *	+ '"' => '\"'
 *	+ "'" => "\\'"
 *
 * @param  string $script String that needs to get escaped.
 * @return string Escaped string.
 */
	public function escape($string) {
		$escape = array("\r\n" => '\n', "\r" => '\n', "\n" => '\n', '"' => '\"', "'" => "\\'");
		return str_replace(array_keys($escape), array_values($escape), $string);
	}

	public function get__($name) {
		return $this->__object($name, 'id');
	}

	public function select($pattern) {
		return $this->__object($pattern, 'pattern');
	}

	public function real($var) {
		return $this->__object($var, 'real');
	}

	private function __object($name, $var) {
		if (!isset($this->__objects[$name])) {
			$this->__objects[$name] = new JsHelperObject($this);
			$this->__objects[$name]->{$var} = $name;
		}
		return $this->__objects[$name];
	}

/**
 * Generates a JavaScript object in JavaScript Object Notation (JSON)
 * from an array
 *
 * @param array $data Data to be converted
 * @param boolean $block Wraps return value in a <script/> block if true
 * @param string $prefix Prepends the string to the returned data
 * @param string $postfix Appends the string to the returned data
 * @param array $stringKeys A list of array keys to be treated as a string
 * @param boolean $quoteKeys If false, treats $stringKey as a list of keys *not* to be quoted
 * @param string $q The type of quote to use
 * @return string A JSON code block
 */
	public function object($data = array(), $block = false, $prefix = '', $postfix = '', $stringKeys = array(), $quoteKeys = true, $q = "\"") {
		if (is_object($data)) {
			$data = get_object_vars($data);
		}

		$out = array();
		$key = array();

		if (is_array($data)) {
			$keys = array_keys($data);
		}

		$numeric = true;

		if (!empty($keys)) {
			foreach ($keys as $key) {
				if (!is_numeric($key)) {
					$numeric = false;
					break;
				}
			}
		}

		foreach ($data as $key => $val) {
			if (is_array($val) || is_object($val)) {
				$val = $this->object($val, false, '', '', $stringKeys, $quoteKeys, $q);
			} else {
				if ((!count($stringKeys) && !is_numeric($val) && !is_bool($val)) || ($quoteKeys && in_array($key, $stringKeys)) || (!$quoteKeys && !in_array($key, $stringKeys)) && $val !== null) {
					$val = $q . $this->escapeString($val) . $q;
				}
				if ($val == null) {
					$val = 'null';
				}
			}

			if (!$numeric) {
				$val = $q . $key . $q . ':' . $val;
			}

			$out[] = $val;
		}

		if (!$numeric) {
			$rt = '{' . join(', ', $out) . '}';
		} else {
			$rt = '[' . join(', ', $out) . ']';
		}
		$rt = $prefix . $rt . $postfix;

		if ($block) {
			$rt = $this->codeBlock($rt);
		}

		return $rt;
	}
}

class JsHelperObject {
	private $__parent = null;

	private $id = null;

	private $pattern = null;

	private $real = null;

	private function __construct(&$parent) {
		if (is_object($parent)) {
			$this->setParent($parent);
		}
	}

	public function toString() {
		return $this->__toString();
	}

	private function __toString() {
		return $this->literal;
	}

	public function ref($ref = null) {
		if ($ref == null) {
			foreach (array('id', 'pattern', 'real') as $ref) {
				if ($this->{$ref} !== null) {
					return $this->{$ref};
				}
			}
		} else {
			return ($this->{$ref} !== null);
		}
		return null;
	}

	public function literal($append = null) {
		if (!empty($this->id)) {
			$data = '$("' . $this->id . '")';
		}
		if (!empty($this->pattern)) {
			$data = '$$("' . $this->pattern . '")';
		}
		if (!empty($this->real)) {
			$data = $this->real;
		}
		if (!empty($append)) {
			$data .= '.' . $append;
		}
		return $data;
	}

	private function __call($name, $args) {
		$data = '';

		if (isset($this->__parent->effectMap[strtolower($name)])) {
			array_unshift($args, $this->__parent->effectMap[strtolower($name)]);
			$name = 'effect';
		}

		switch ($name) {
			case 'effect':
			case 'visualEffect':

				if (strpos($args[0], '_') || $args[0]{0} != strtoupper($args[0]{0})) {
					$args[0] = Inflector::camelize($args[0]);
				}

				if (strtolower($args[0]) == 'highlight') {
					$data .= 'new ';
				}
				if ($this->pattern == null) {
					$data .= 'Effect.' . $args[0] . '(' . $this->literal();
				} else {
					$data .= 'Effect.' . $args[0] . '(item';
				}

				if (isset($args[1]) && is_array($args[1])) {
					$data .= ', {' . $this->__options($args[1]) . '}';
				}
				$data .= ');';

				if ($this->pattern !== null) {
					$data = $this->each($data);
				}
			break;
			case 'remove':
			case 'toggle':
			case 'show':
			case 'hide':
				if (empty($args)) {
					$obj = 'Element';
					$params = '';
				} else {
					$obj = 'Effect';
					$params = ', "' . $args[0] . '"';
				}

				if ($this->pattern != null) {
					$data = $this->each($obj . ".{$name}(item);");
				} else {
					$data = $obj . ".{$name}(" . $this->literal() . ');';
				}
			break;
			case 'visible':
				$data = $this->literal() . '.visible();';
			break;
			case 'update':
				$data = $this->literal() . ".update({$args[0]});";
			break;
			case 'load':
				$data = 'new Ajax.Updater("' . $this->id . '", "' . $args[0] . '"';
				if (isset($args[1]) && is_array($args[1])) {
					$data .= ', {' . $this->__options($args[1]) . '}';
				}
				$data .= ');';
			break;
			case 'each':
			case 'all':
			case 'any':
			case 'detect':
			case 'findAll':
				if ($this->pattern != null) {
					$data = $this->__iterate($name, $args[0]);
				}
			break;
			case 'addClass':
			case 'removeClass':
			case 'hasClass':
			case 'toggleClass':
				$data = $this->literal() . ".{$name}Name(\"{$args[0]}\");";
			break;
			case 'clone':
			case 'inspect':
			case 'keys':
			case 'values':
				$data = "Object.{$name}(" . $this->literal() . ");";
			break;
			case 'extend':
				$data = "Object.extend(" . $this->literal() . ", {$args[0]});";
			break;
			case '...':
				// Handle other methods here
				// including interfaces to load other files on-the-fly
				// that add support for additional methods/replacing existing methods
			break;
			default:
				$data = $this->literal() . '.' . $name . '();';
			break;
		}

		if ($this->__parent->output) {
			echo $data;
		} else {
			return $data;
		}
	}

	private function __iterate($method, $data) {
		return '$$("' . $this->pattern . '").' . $method . '(function(item) {' . $data . '});';
	}

	public function setParent(&$parent) {
		$this->__parent = $parent;
	}

	private function __options($opts) {
		$options = array();
		foreach ($opts as $key => $val) {
			if (!is_int($val)) {
				$val = '"' . $val . '"';
			}
			$options[] = $key . ':' . $val;
		}
		return join(', ', $options);
	}
}
?>