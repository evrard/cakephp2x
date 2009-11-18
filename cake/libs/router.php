<?php
/**
 * Parses the request URL into controller, action, and parameters.
 *
 * Long description for file
 *
 * PHP Version 5.x
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2009, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2009, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.cake.libs
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Parses the request URL into controller, action, and parameters.
 *
 * @package       cake
 * @subpackage    cake.cake.libs
 */
class Router {
	const ACTION = 'index|show|add|create|edit|update|remove|del|delete|view|item';
	const YEAR = '[12][0-9]{3}';
	const MONTH = '0[1-9]|1[012]';
	const DAY = '0[1-9]|[12][0-9]|3[01]';
	const ID = '[0-9]+';
	const UUID = '[A-Fa-f0-9]{8}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{12}';

/**
 * Array of routes
 *
 * @var array
 * @access public
 */
	public static $routes = array();

/**
 * List of action prefixes used in connected routes.
 * Includes admin prefix
 *
 * @var array
 * @access private
 */
	private static $__prefixes = array();

/**
 * Directive for Router to parse out file extensions for mapping to Content-types.
 *
 * @var boolean
 * @access private
 */
	private static $__parseExtensions = false;

/**
 * List of valid extensions to parse from a URL.  If null, any extension is allowed.
 *
 * @var array
 * @access private
 */
	private static $__validExtensions = null;

/**
 * 'Constant' regular expression definitions for named route elements
 *
 * @var array
 * @access private
 */
	private $__named = array(
		'Action'	=> 'index|show|add|create|edit|update|remove|del|delete|view|item',
		'Year'		=> '[12][0-9]{3}',
		'Month'		=> '0[1-9]|1[012]',
		'Day'		=> '0[1-9]|[12][0-9]|3[01]',
		'ID'		=> '[0-9]+',
		'UUID'		=> '[A-Fa-f0-9]{8}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{12}'
	);

/**
 * Stores all information necessary to decide what named arguments are parsed under what conditions.
 *
 * @var string
 * @access public
 */
	public static $named = array(
		'default' => array('page', 'fields', 'order', 'limit', 'recursive', 'sort', 'direction', 'step'),
		'greedy' => true,
		'separator' => ':',
		'rules' => false,
	);

/**
 * The route matching the URL of the current request
 *
 * @var array
 * @access private
 */
	private static $__currentRoute = array();

/**
 * HTTP header shortcut map.  Used for evaluating header-based route expressions.
 *
 * @var array
 * @access private
 */
	private static $__headerMap = array(
		'type'		=> 'content_type',
		'method'	=> 'request_method',
		'server'	=> 'server_name'
	);

/**
 * Maintains the parameter stack for the current request
 *
 * @var array
 * @access private
 */
	private static $__params = array();

/**
 * Maintains the path stack for the current request
 *
 * @var array
 * @access private
 */
	private static $__paths = array();

/**
 * Keeps Router state to determine if default routes have already been connected
 *
 * @var boolean
 * @access private
 */
	private static $__defaultsMapped = false;

/**
 * Constructor for Router.
 * Builds __prefixes
 *
 * @return void
 */
	public function init() {
		self::__setPrefixes();
	}

/**
 * Sets the Routing prefixes. Includes compatibilty for existing Routing.admin
 * configurations.
 *
 * @return void
 * @access private
 * @todo Remove support for Routing.admin in future versions.
 */
	private function __setPrefixes() {
		$routing = Configure::read('Routing');
		if (!empty($routing['admin'])) {
			self::$__prefixes[] = $routing['admin'];
		}
		if (!empty($routing['prefixes'])) {
			self::$__prefixes = array_merge(self::$__prefixes, (array)$routing['prefixes']);
		}
	}

/**
 * Returns this object's routes array. Returns false if there are no routes available.
 *
 * @param string $route An empty string, or a route string "/"
 * @param array $default NULL or an array describing the default route
 * @param array $params An array matching the named elements in the route to regular expressions which that element should match.
 * @see routes
 * @return array Array of routes
 * @access public
 * @static
 */
	public static function connect($route, $default = array(), $params = array()) {
		if (!isset($default['action'])) {
			$default['action'] = 'index';
		}
		foreach (self::$__prefixes as $prefix) {
			if (isset($default[$prefix])) {
				$default['prefix'] = $prefix;
				break;
			}
		}
		if (isset($default['prefix'])) {
			self::$__prefixes[] = $default['prefix'];
			self::$__prefixes = array_keys(array_flip(self::$__prefixes));
		}
		self::$routes[] = array($route, $default, $params);
		return self::$routes;
	}

/**
 * Specifies what named parameters CakePHP should be parsing. The most common setups are:
 *
 * Do not parse any named parameters:
 * {{{ Router::connectNamed(false); }}}
 *
 * Parse only default parameters used for CakePHP's pagination:
 * {{{ Router::connectNamed(false, array('default' => true)); }}}
 *
 * Parse only the page parameter if its value is a number:
 * {{{ Router::connectNamed(array('page' => '[\d]+'), array('default' => false, 'greedy' => false)); }}}
 *
 * Parse only the page parameter no mater what.
 * {{{ Router::connectNamed(array('page'), array('default' => false, 'greedy' => false)); }}}
 *
 * Parse only the page parameter if the current action is 'index'.
 * {{{ Router::connectNamed(array('page' => array('action' => 'index')), array('default' => false, 'greedy' => false)); }}}
 *
 * Parse only the page parameter if the current action is 'index' and the controller is 'pages'.
 * {{{ Router::connectNamed(array('page' => array('action' => 'index', 'controller' => 'pages')), array('default' => false, 'greedy' => false)); }}}
 *
 * @param array $named A list of named parameters. Key value pairs are accepted where values are either regex strings to match, or arrays as seen above.
 * @param array $options Allows to control all settings: separator, greedy, reset, default
 * @return array
 * @access public
 * @static
 */
	public static function connectNamed($named, $options = array()) {
		if (isset($options['argSeparator'])) {
			self::$named['separator'] = $options['argSeparator'];
			unset($options['argSeparator']);
		}

		if ($named === true || $named === false) {
			$options = array_merge(array('default' => $named, 'reset' => true, 'greedy' => $named), $options);
			$named = array();
		}
		$options = array_merge(array('default' => false, 'reset' => false, 'greedy' => true), $options);

		if ($options['reset'] == true || self::$named['rules'] === false) {
			self::$named['rules'] = array();
		}

		if ($options['default']) {
			$named = array_merge($named, self::$named['default']);
		}

		foreach ($named as $key => $val) {
			if (is_numeric($key)) {
				self::$named['rules'][$val] = true;
			} else {
				self::$named['rules'][$key] = $val;
			}
		}
		self::$named['greedy'] = $options['greedy'];
		return self::$named;
	}

/**
 * Creates REST resource routes for the given controller(s)
 *
 * Options:
 *
 * - 'id' - The regular expression fragment to use when matching IDs.  By default, matches
 *    integer values and UUIDs.
 * - 'prefix' - URL prefix to use for the generated routes.  Defaults to '/'.
 *
 * @param mixed $controller A controller name or array of controller names (i.e. "Posts" or "ListItems")
 * @param array $options Options to use when generating REST routes
 * @return void
 * @access public
 * @static
 */
	public static function mapResources($controller, $options = array()) {
		$options = array_merge(array('prefix' => '/', 'id' => self::ID . '|' . self::UUID), $options);
		$prefix = $options['prefix'];

		foreach ((array)$controller as $ctlName) {
			$urlName = Inflector::underscore($ctlName);
			
			$resourceMap =	array(
				array('action' => 'index',	'method' => 'GET',		'id' => false),
				array('action' => 'view',	'method' => 'GET',		'id' => true),
				array('action' => 'add',	'method' => 'POST',		'id' => false),
				array('action' => 'edit',	'method' => 'PUT', 		'id' => true),
				array('action' => 'delete',	'method' => 'DELETE',	'id' => true),
				array('action' => 'edit',	'method' => 'POST', 	'id' => true));

			foreach ($resourceMap as $params) {
				extract($params);
				$url = $prefix . $urlName . (($id) ? '/:id' : '');

				self::connect($url,
					array('controller' => $urlName, 'action' => $action, '[method]' => $params['method']),
					array('id' => $options['id'], 'pass' => array('id'))
				);
			}
		}
	}

/**
 * Builds a route regular expression
 *
 * @param string $route An empty string, or a route string "/"
 * @param array $default NULL or an array describing the default route
 * @param array $params An array matching the named elements in the route to regular expressions which that element should match.
 * @return array
 * @see routes
 * @access public
 * @static
 */
	public function writeRoute($route, $default, $params) {
		if (empty($route) || ($route === '/')) {
			return array('/^[\/]*$/', array());
		}
		$names = array();
		$elements = explode('/', $route);

		foreach ($elements as $element) {
			if (empty($element)) {
				continue;
			}
			$q = null;
			$element = trim($element);
			$namedParam = strpos($element, ':') !== false;

			if ($namedParam && preg_match('/^:([^:]+)$/', $element, $r)) {
				if (isset($params[$r[1]])) {
					if ($r[1] != 'plugin' && array_key_exists($r[1], $default)) {
						$q = '?';
					}
					$parsed[] = '(?:/(' . $params[$r[1]] . ')' . $q . ')' . $q;
				} else {
					$parsed[] = '(?:/([^\/]+))?';
				}
				$names[] = $r[1];
			} elseif ($element === '*') {
				$parsed[] = '(?:/(.*))?';
			} else if ($namedParam && preg_match_all('/(?!\\\\):([a-z_0-9]+)/i', $element, $matches)) {
				$matchCount = count($matches[1]);

				foreach ($matches[1] as $i => $name) {
					$pos = strpos($element, ':' . $name);
					$before = substr($element, 0, $pos);
					$element = substr($element, $pos + strlen($name) + 1);
					$after = null;

					if ($i + 1 === $matchCount && $element) {
						$after = preg_quote($element);
					}

					if ($i === 0) {
						$before = '/' . $before;
					}
					$before = preg_quote($before, '#');

					if (isset($params[$name])) {
						if (isset($default[$name]) && $name != 'plugin') {
							$q = '?';
						}
						$parsed[] = '(?:' . $before . '(' . $params[$name] . ')' . $q . $after . ')' . $q;
					} else {
						$parsed[] = '(?:' . $before . '([^\/]+)' . $after . ')?';
					}
					$names[] = $name;
				}
			} else {
				$parsed[] = '/' . $element;
			}
		}
		return array('#^' . join('', $parsed) . '[\/]*$#', $names);
	}

/**
 * Returns the list of prefixes used in connected routes
 *
 * @return array A list of prefixes used in connected routes
 * @access public
 * @static
 */
	public static function prefixes() {
		return self::$__prefixes;
	}

/**
 * Parses given URL and returns an array of controllers, action and parameters
 * taken from that URL.
 *
 * @param string $url URL to be parsed
 * @return array Parsed elements from URL
 * @access public
 * @static
 */
	public function parse($url) {
		if (!self::$__defaultsMapped) {
			self::__connectDefaultRoutes();
		}
		$out = array('pass' => array(), 'named' => array());
		$r = $ext = null;

		if (ini_get('magic_quotes_gpc') === '1') {
			$url = stripslashes_deep($url);
		}

		if ($url && strpos($url, '/') !== 0) {
			$url = '/' . $url;
		}
		if (strpos($url, '?') !== false) {
			$url = substr($url, 0, strpos($url, '?'));
		}
		extract(self::__parseExtension($url));

		foreach (self::$routes as $i => $route) {
			if (count($route) === 3) {
				$route = self::compile($i);
			}

			if (($r = self::__matchRoute($route, $url)) !== false) {
				self::$__currentRoute[] = $route;
				list($route, $regexp, $names, $defaults, $params) = $route;
				$argOptions = array();

				if (array_key_exists('named', $params)) {
					$argOptions['named'] = $params['named'];
					unset($params['named']);
				}
				if (array_key_exists('greedy', $params)) {
					$argOptions['greedy'] = $params['greedy'];
					unset($params['greedy']);
				}
				array_shift($r);

				foreach ($names as $name) {
					$out[$name] = null;
				}
				if (is_array($defaults)) {
					foreach ($defaults as $name => $value) {
						if (preg_match('#[a-zA-Z_\-]#i', $name)) {
							$out[$name] = $value;
						} else {
							$out['pass'][] = $value;
						}
					}
				}

				foreach ($r as $key => $found) {
					if (empty($found) && $found != 0) {
						continue;
					}

					if (isset($names[$key])) {
						$out[$names[$key]] = self::stripEscape($found);
					} else {
						$argOptions['context'] = array('action' => $out['action'], 'controller' => $out['controller']);
						extract(self::getArgs($found, $argOptions));
						$out['pass'] = array_merge($out['pass'], $pass);
						$out['named'] = $named;
					}
				}

				if (isset($params['pass'])) {
					for ($j = count($params['pass']) - 1; $j > -1; $j--) {
						if (isset($out[$params['pass'][$j]])) {
							array_unshift($out['pass'], $out[$params['pass'][$j]]);
						}
					}
				}
				break;
			}
		}

		if (!empty($ext)) {
			$out['url']['ext'] = $ext;
		}
		return $out;
	}

/**
 * Checks to see if the given URL matches the given route
 *
 * @param array $route
 * @param string $url
 * @return mixed Boolean false on failure, otherwise array
 * @access private
 */
	private static function __matchRoute($route, $url) {
		list($route, $regexp, $names, $defaults) = $route;

		if (!preg_match($regexp, $url, $r)) {
			return false;
		} else {
			foreach ($defaults as $key => $val) {
				if ($key{0} === '[' && preg_match('/^\[(\w+)\]$/', $key, $header)) {
					if (isset(self::$__headerMap[$header[1]])) {
						$header = self::$__headerMap[$header[1]];
					} else {
						$header = 'http_' . $header[1];
					}

					$val = (array)$val;
					$h = false;

					foreach ($val as $v) {
						if (env(strtoupper($header)) === $v) {
							$h = true;
						}
					}
					if (!$h) {
						return false;
					}
				}
			}
		}
		return $r;
	}

/**
 * Compiles a route by numeric key and returns the compiled expression, replacing
 * the existing uncompiled route.  Do not call statically.
 *
 * @param integer $i
 * @return array Returns an array containing the compiled route
 * @access public
 */
	public static function compile($i) {
		$route = self::$routes[$i];

		list($pattern, $names) = self::writeRoute($route[0], $route[1], $route[2]);
		self::$routes[$i] = array(
			$route[0], $pattern, $names,
			array_merge(array('plugin' => null, 'controller' => null), (array)$route[1]),
			$route[2]
		);
		return self::$routes[$i];
	}

/**
 * Parses a file extension out of a URL, if Router::parseExtensions() is enabled.
 *
 * @param string $url
 * @return array Returns an array containing the altered URL and the parsed extension.
 * @access private
 */
	private static function __parseExtension($url) {
		$ext = null;

		if (self::$__parseExtensions) {
			if (preg_match('/\.[0-9a-zA-Z]*$/', $url, $match) === 1) {
				$match = substr($match[0], 1);
				if (empty(self::$__validExtensions)) {
					$url = substr($url, 0, strpos($url, '.' . $match));
					$ext = $match;
				} else {
					foreach (self::$__validExtensions as $name) {
						if (strcasecmp($name, $match) === 0) {
							$url = substr($url, 0, strpos($url, '.' . $name));
							$ext = $match;
							break;
						}
					}
				}
			}
			if (empty($ext)) {
				$ext = 'html';
			}
		}
		return compact('ext', 'url');
	}

/**
 * Connects the default, built-in routes, including admin routes, and (deprecated) web services
 * routes.
 *
 * @return void
 * @access private
 */
	private static function __connectDefaultRoutes() {
		if (self::$__defaultsMapped) {
			return;
		}

		if ($plugins = App::objects('plugin')) {
			foreach ($plugins as $key => $value) {
				$plugins[$key] = Inflector::underscore($value);
			}

			$match = array('plugin' => implode('|', $plugins));
			self::connect('/:plugin/:controller/:action/*', array(), $match);

			foreach (self::$__prefixes as $prefix) {
				$params = array('prefix' => $prefix, $prefix => true);
				self::connect("/{$prefix}/:plugin/:controller", $params, $match);
				self::connect("/{$prefix}/:plugin/:controller/:action/*", $params, $match);
			}
		}

		foreach (self::$__prefixes as $prefix) {
			$params = array('prefix' => $prefix, $prefix => true);
			self::connect("/{$prefix}/:controller", $params);
			self::connect("/{$prefix}/:controller/:action/*", $params);
		}
		self::connect('/:controller', array('action' => 'index'));
		self::connect('/:controller/:action/*');

		if (self::$named['rules'] === false) {
			self::connectNamed(true);
		}
		self::$__defaultsMapped = true;
	}

/**
 * Takes parameter and path information back from the Dispatcher
 *
 * @param array $params Parameters and path information
 * @return void
 * @access public
 * @static
 */
	public static function setRequestInfo($params) {
		$defaults = array('plugin' => null, 'controller' => null, 'action' => null);
		$params[0] = array_merge($defaults, (array)$params[0]);
		$params[1] = array_merge($defaults, (array)$params[1]);
		list(self::$__params[], self::$__paths[]) = $params;

		if (count(self::$__paths)) {
			if (isset(self::$__paths[0]['namedArgs'])) {
				foreach (self::$__paths[0]['namedArgs'] as $arg => $value) {
					self::$named['rules'][$arg] = true;
				}
			}
		}
	}

/**
 * Gets parameter information
 *
 * @param boolean $current Get current parameter (true)
 * @return array Parameter information
 * @access public
 * @static
 */
	public static function getParams($current = false) {
		if ($current) {
			return self::$__params[count(self::$__params) - 1];
		}
		if (isset(self::$__params[0])) {
			return self::$__params[0];
		}
		return array();
	}

/**
 * Gets URL parameter by name
 *
 * @param string $name Parameter name
 * @param boolean $current Current parameter
 * @return string Parameter value
 * @access public
 * @static
 */
	public static function getParam($name = 'controller', $current = false) {
		$params = self::getParams($current);
		if (isset($params[$name])) {
			return $params[$name];
		}
		return null;
	}

/**
 * Gets path information
 *
 * @param boolean $current Current parameter
 * @return array
 * @access public
 * @static
 */
	public static function getPaths($current = false) {
		if ($current) {
			return self::$__paths[count(self::$__paths) - 1];
		}
		if (!isset(self::$__paths[0])) {
			return array('base' => null);
		}
		return self::$__paths[0];
	}

/**
 * Reloads default Router settings
 *
 * @access public
 * @return void
 * @static
 */
	public static function reload() {
		self::$routes = array();
		self::$__prefixes = array();
		self::$__parseExtensions = false;
		self::$__validExtensions = null;
		self::$named = array(
			'default' => array('page', 'fields', 'order', 'limit', 'recursive', 'sort', 'direction', 'step'),
			'greedy' => true,
			'separator' => ':',
			'rules' => false);
		self::$__currentRoute = array();
		self::$__headerMap = array(
			'type'		=> 'content_type',
			'method'	=> 'request_method',
			'server'	=> 'server_name');
		self::$__params = array();
		self::$__paths = array();
		self::$__defaultsMapped = false;
		
		self::init();
	}

/**
 * Promote a route (by default, the last one added) to the beginning of the list
 *
 * @param $which A zero-based array index representing the route to move. For example,
 *               if 3 routes have been added, the last route would be 2.
 * @return boolean Retuns false if no route exists at the position specified by $which.
 * @access public
 * @static
 */
	public static function promote($which = null) {
		if ($which === null) {
			$which = count(self::$routes) - 1;
		}
		if (!isset(self::$routes[$which])) {
			return false;
		}
		$route = self::$routes[$which];
		unset(self::$routes[$which]);
		array_unshift(self::$routes, $route);
		return true;
	}

/**
 * Finds URL for specified action.
 *
 * Returns an URL pointing to a combination of controller and action. Param
 * $url can be:
 *
 * - Empty - the method will find adress to actuall controller/action.
 * - '/' - the method will find base URL of application.
 * - A combination of controller/action - the method will find url for it.
 *
 * @param mixed $url Cake-relative URL, like "/products/edit/92" or "/presidents/elect/4"
 *   or an array specifying any of the following: 'controller', 'action',
 *   and/or 'plugin', in addition to named arguments (keyed array elements),
 *   and standard URL arguments (indexed array elements)
 * @param mixed $full If (bool) true, the full base URL will be prepended to the result.
 *   If an array accepts the following keys
 *    - escape - used when making urls embedded in html escapes query string '&'
 *    - full - if true the full base URL will be prepended.
 * @return string Full translated URL with base path.
 * @access public
 * @static
 */
	public static function url($url = null, $full = false) {
		$defaults = $params = array('plugin' => null, 'controller' => null, 'action' => 'index');

		if (is_bool($full)) {
			$escape = false;
		} else {
			extract(array_merge(array('escape' => false, 'full' => false), $full));
		}

		if (!empty(self::$__params)) {
			if (!isset(self::$__params['requested'])) {
				$params = self::$__params[0];
			} else {
				$params = end(self::$__params);
			}
			if (isset($params['prefix']) && strpos($params['action'], $params['prefix']) === 0) {
				$params['action'] = substr($params['action'], strlen($params['prefix']) + 1);
			}
		}
		$path = array('base' => null);

		if (!empty(self::$__paths)) {
			if (!isset(self::$__params['requested'])) {
				$path = self::$__paths[0];
			} else {
				$path = end(self::$__paths);
			}
		}
		$base = $path['base'];
		$extension = $output = $mapped = $q = $frag = null;

		if (is_array($url)) {
			if (isset($url['base']) && $url['base'] === false) {
				$base = null;
				unset($url['base']);
			}
			if (isset($url['full_base']) && $url['full_base'] === true) {
				$full = true;
				unset($url['full_base']);
			}
			if (isset($url['?'])) {
				$q = $url['?'];
				unset($url['?']);
			}
			if (isset($url['#'])) {
				$frag = '#' . urlencode($url['#']);
				unset($url['#']);
			}
			if (empty($url['action'])) {
				if (empty($url['controller']) || $params['controller'] === $url['controller']) {
					$url['action'] = $params['action'];
				} else {
					$url['action'] = 'index';
				}
			}

			$prefixExists = (array_intersect_key($url, array_flip(self::$__prefixes)));
			foreach (self::$__prefixes as $prefix) {
				if (!isset($url[$prefix]) && !empty($params[$prefix]) && !$prefixExists) {
					$url[$prefix] = true;
				} elseif (isset($url[$prefix]) && !$url[$prefix]) {
					unset($url[$prefix]);
				}
			}
			$plugin = false;

			if (array_key_exists('plugin', $url)) {
				$plugin = $url['plugin'];
			}

			$_url = $url;
			$url = array_merge(array('controller' => $params['controller'], 'plugin' => $params['plugin']), Set::filter($url, true));

			if ($plugin !== false) {
				$url['plugin'] = $plugin;
			}

			if (isset($url['ext'])) {
				$extension = '.' . $url['ext'];
				unset($url['ext']);
			}
			$match = false;

			foreach (self::$routes as $i => $route) {
				if (count($route) === 3) {
					$route = self::compile($i);
				}
				$originalUrl = $url;

				if (isset($route[4]['persist'], self::$__params[0])) {
					foreach($route[4]['persist'] as $_key) {
						if (array_key_exists($_key, $_url)) {
							$url[$_key] = $_url[$_key];
						} elseif (array_key_exists($_key, $params)) {
							$url[$_key] = $params[$_key];
						}
					}
				}
				if ($match = self::mapRouteElements($route, $url)) {
					$output = trim($match, '/');
					$url = array();
					break;
				}
				$url = $originalUrl;
			}

			$named = $args = array();
			$skip = array_merge(
				array('bare', 'action', 'controller', 'plugin', 'ext', '?', '#', 'prefix'),
				self::$__prefixes
			);

			$keys = array_values(array_diff(array_keys($url), $skip));
			$count = count($keys);

			// Remove this once parsed URL parameters can be inserted into 'pass'
			for ($i = 0; $i < $count; $i++) {
				if (is_numeric($keys[$i])) {
					$args[] = $url[$keys[$i]];
				} else {
					$named[$keys[$i]] = $url[$keys[$i]];
				}
			}

			if ($match === false) {
				list($args, $named)  = array(Set::filter($args, true), Set::filter($named));
				foreach (self::$__prefixes as $prefix) {
					if (!empty($url[$prefix])) {
						$url['action'] = str_replace($prefix . '_', '', $url['action']);
						break;
					}
				}

				if (empty($named) && empty($args) && (!isset($url['action']) || $url['action'] === 'index')) {
					$url['action'] = null;
				}

				$urlOut = Set::filter(array($url['controller'], $url['action']));

				if (isset($url['plugin']) && $url['plugin'] != $url['controller']) {
					array_unshift($urlOut, $url['plugin']);
				}

				foreach (self::$__prefixes as $prefix) {
					if (isset($url[$prefix])) {
						array_unshift($urlOut, $prefix);
						break;
					}
				}
				$output = join('/', $urlOut) . '/';
			}

			if (!empty($args)) {
				$args = join('/', $args);
				if ($output{strlen($output) - 1} != '/') {
					$args = '/'. $args;
				}
				$output .= $args;
			}

			if (!empty($named)) {
				foreach ($named as $name => $value) {
					$output .= '/' . $name . self::$named['separator'] . $value;
				}
			}
			$output = str_replace('//', '/', $base . '/' . $output);
		} else {
			if (((strpos($url, '://')) || (strpos($url, 'javascript:') === 0) || (strpos($url, 'mailto:') === 0)) || (!strncmp($url, '#', 1))) {
				return $url;
			}
			if (empty($url)) {
				if (!isset($path['here'])) {
					$path['here'] = '/';
				}
				$output = $path['here'];
			} elseif (substr($url, 0, 1) === '/') {
				$output = $base . $url;
			} else {
				$output = $base . '/';
				foreach (self::$__prefixes as $prefix) {
					if (isset($params[$prefix])) {
						$output .= $prefix . '/';
						break;
					}
				}
				if (!empty($params['plugin']) && $params['plugin'] !== $params['controller']) {
					$output .= Inflector::underscore($params['plugin']) . '/';
				}
				$output .= Inflector::underscore($params['controller']) . '/' . $url;
			}
			$output = str_replace('//', '/', $output);
		}
		if ($full && defined('FULL_BASE_URL')) {
			$output = FULL_BASE_URL . $output;
		}
		if (!empty($extension) && substr($output, -1) === '/') {
			$output = substr($output, 0, -1);
		}

		return $output . $extension . self::queryString($q, array(), $escape) . $frag;
	}

/**
 * Maps a URL array onto a route and returns the string result, or false if no match
 *
 * @param array $route Route Route
 * @param array $url URL URL to map
 * @return mixed Result (as string) or false if no match
 * @access public
 * @static
 */
	public static function mapRouteElements($route, $url) {
		if (isset($route[3]['prefix'])) {
			$prefix = $route[3]['prefix'];
			unset($route[3]['prefix']);
		}

		$pass = array();
		$defaults = $route[3];
		$routeParams = $route[2];
		$params = Set::diff($url, $defaults);
		$urlInv = array_combine(array_values($url), array_keys($url));

		$i = 0;
		while (isset($defaults[$i])) {
			if (isset($urlInv[$defaults[$i]])) {
				if (!in_array($defaults[$i], $url) && is_int($urlInv[$defaults[$i]])) {
					return false;
				}
				unset($urlInv[$defaults[$i]], $defaults[$i]);
			} else {
				return false;
			}
			$i++;
		}

		foreach ($params as $key => $value) {
			if (is_int($key)) {
				$pass[] = $value;
				unset($params[$key]);
			}
		}
		list($named, $params) = self::getNamedElements($params);

		if (!strpos($route[0], '*') && (!empty($pass) || !empty($named))) {
			return false;
		}

		$urlKeys = array_keys($url);
		$paramsKeys = array_keys($params);
		$defaultsKeys = array_keys($defaults);

		if (!empty($params)) {
			if (array_diff($paramsKeys, $routeParams) != array()) {
				return false;
			}
			$required = array_values(array_diff($routeParams, $urlKeys));
			$reqCount = count($required);

			for ($i = 0; $i < $reqCount; $i++) {
				if (array_key_exists($required[$i], $defaults) && $defaults[$required[$i]] === null) {
					unset($required[$i]);
				}
			}
		}
		$isFilled = true;

		if (!empty($routeParams)) {
			$filled = array_intersect_key($url, array_combine($routeParams, array_keys($routeParams)));
			$isFilled = (array_diff($routeParams, array_keys($filled)) === array());
			if (!$isFilled && empty($params)) {
				return false;
			}
		}

		if (empty($params)) {
			return self::__mapRoute($route, array_merge($url, compact('pass', 'named', 'prefix')));
		} elseif (!empty($routeParams) && !empty($route[3])) {

			if (!empty($required)) {
				return false;
			}
			foreach ($params as $key => $val) {
				if ((!isset($url[$key]) || $url[$key] != $val) || (!isset($defaults[$key]) || $defaults[$key] != $val) && !in_array($key, $routeParams)) {
					if (!isset($defaults[$key])) {
						continue;
					}
					return false;
				}
			}
		} else {
			if (empty($required) && $defaults['plugin'] === $url['plugin'] && $defaults['controller'] === $url['controller'] && $defaults['action'] === $url['action']) {
				return self::__mapRoute($route, array_merge($url, compact('pass', 'named', 'prefix')));
			}
			return false;
		}

		if (!empty($route[4])) {
			foreach ($route[4] as $key => $reg) {
				if (array_key_exists($key, $url) && !preg_match('#' . $reg . '#', $url[$key])) {
					return false;
				}
			}
		}
		return self::__mapRoute($route, array_merge($filled, compact('pass', 'named', 'prefix')));
	}

/**
 * Merges URL parameters into a route string
 *
 * @param array $route Route
 * @param array $params Parameters
 * @return string Merged URL with parameters
 * @access private
 */
	private static function __mapRoute($route, $params = array()) {
		if (isset($params['plugin']) && isset($params['controller']) && $params['plugin'] === $params['controller']) {
			unset($params['controller']);
		}

		if (isset($params['prefix']) && isset($params['action'])) {
			$params['action'] = str_replace($params['prefix'] . '_', '', $params['action']);
			unset($params['prefix']);
		}

		if (isset($params['pass']) && is_array($params['pass'])) {
			$params['pass'] = implode('/', Set::filter($params['pass'], true));
		} elseif (!isset($params['pass'])) {
			$params['pass'] = '';
		}

		if (isset($params['named'])) {
			if (is_array($params['named'])) {
				$count = count($params['named']);
				$keys = array_keys($params['named']);
				$named = array();

				for ($i = 0; $i < $count; $i++) {
					$named[] = $keys[$i] . self::$named['separator'] . $params['named'][$keys[$i]];
				}
				$params['named'] = join('/', $named);
			}
			$params['pass'] = str_replace('//', '/', $params['pass'] . '/' . $params['named']);
		}
		$out = $route[0];

		foreach ($route[2] as $key) {
			$string = null;
			if (isset($params[$key])) {
				$string = $params[$key];
				unset($params[$key]);
			} elseif (strpos($out, $key) != strlen($out) - strlen($key)) {
				$key = $key . '/';
			}
			$out = str_replace(':' . $key, $string, $out);
		}

		if (strpos($route[0], '*')) {
			$out = str_replace('*', $params['pass'], $out);
		}

		return $out;
	}

/**
 * Takes an array of URL parameters and separates the ones that can be used as named arguments
 *
 * @param array $params			Associative array of URL parameters.
 * @param string $controller	Name of controller being routed.  Used in scoping.
 * @param string $action	 	Name of action being routed.  Used in scoping.
 * @return array
 * @access public
 * @static
 */
	public function getNamedElements($params, $controller = null, $action = null) {
		$named = array();

		foreach ($params as $param => $val) {
			if (isset(self::$named['rules'][$param])) {
				$rule = self::$named['rules'][$param];
				if (self::matchNamed($param, $val, $rule, compact('controller', 'action'))) {
					$named[$param] = $val;
					unset($params[$param]);
				}
			}
		}
		return array($named, $params);
	}

/**
 * Return true if a given named $param's $val matches a given $rule depending on $context. Currently implemented
 * rule types are controller, action and match that can be combined with each other.
 *
 * @param string $param The name of the named parameter
 * @param string $val The value of the named parameter
 * @param array $rule The rule(s) to apply, can also be a match string
 * @param string $context An array with additional context information (controller / action)
 * @return boolean
 * @access public
 */
	public function matchNamed($param, $val, $rule, $context = array()) {
		if ($rule === true || $rule === false) {
			return $rule;
		}
		if (is_string($rule)) {
			$rule = array('match' => $rule);
		}
		if (!is_array($rule)) {
			return false;
		}

		$controllerMatches = !isset($rule['controller'], $context['controller']) || in_array($context['controller'], (array)$rule['controller']);
		if (!$controllerMatches) {
			return false;
		}
		$actionMatches = !isset($rule['action'], $context['action']) || in_array($context['action'], (array)$rule['action']);
		if (!$actionMatches) {
			return false;
		}
		$valueMatches = !isset($rule['match']) || preg_match(sprintf('/%s/', $rule['match']), $val);
		return $valueMatches;
	}

/**
 * Generates a well-formed querystring from $q
 *
 * @param mixed $q Query string
 * @param array $extra Extra querystring parameters.
 * @param bool $escape Whether or not to use escaped &
 * @return array
 * @access public
 * @static
 */
	public static function queryString($q, $extra = array(), $escape = false) {
		if (empty($q) && empty($extra)) {
			return null;
		}
		$join = '&';
		if ($escape === true) {
			$join = '&amp;';
		}
		$out = '';

		if (is_array($q)) {
			$q = array_merge($extra, $q);
		} else {
			$out = $q;
			$q = $extra;
		}
		$out .= http_build_query($q, null, $join);
		if (isset($out[0]) && $out[0] != '?') {
			$out = '?' . $out;
		}
		return $out;
	}

/**
 * Normalizes a URL for purposes of comparison
 *
 * @param mixed $url URL to normalize
 * @return string Normalized URL
 * @access public
 */
	public static function normalize($url = '/') {
		if (is_array($url)) {
			$url = self::url($url);
		} elseif (preg_match('/^[a-z\-]+:\/\//', $url)) {
			return $url;
		}
		$paths = self::getPaths();

		if (!empty($paths['base']) && stristr($url, $paths['base'])) {
			$url = preg_replace('/^' . preg_quote($paths['base'], '/') . '/', '', $url, 1);
		}
		$url = '/' . $url;

		while (strpos($url, '//') !== false) {
			$url = str_replace('//', '/', $url);
		}
		$url = preg_replace('/(?:(\/$))/', '', $url);

		if (empty($url)) {
			return '/';
		}
		return $url;
	}

/**
 * Returns the route matching the current request URL.
 *
 * @return array Matching route
 * @access public
 * @static
 */
	public static function requestRoute() {
		return self::$__currentRoute[0];
	}

/**
 * Returns the route matching the current request (useful for requestAction traces)
 *
 * @return array Matching route
 * @access public
 * @static
 */
	public static function currentRoute() {
		return self::$__currentRoute[count(self::$__currentRoute) - 1];
	}

/**
 * Removes the plugin name from the base URL.
 *
 * @param string $base Base URL
 * @param string $plugin Plugin name
 * @return base url with plugin name removed if present
 * @access public
 * @static
 */
	public function stripPlugin($base, $plugin = null) {
		if ($plugin != null) {
			$base = preg_replace('/(?:' . $plugin . ')/', '', $base);
			$base = str_replace('//', '', $base);
			$pos1 = strrpos($base, '/');
			$char = strlen($base) - 1;

			if ($pos1 === $char) {
				$base = substr($base, 0, $char);
			}
		}
		return $base;
	}

/**
 * Strip escape characters from parameter values.
 *
 * @param mixed $param Either an array, or a string
 * @return mixed Array or string escaped
 * @access public
 * @static
 */
	public static function stripEscape($param) {
		if (!is_array($param) || empty($param)) {
			if (is_bool($param)) {
				return $param;
			}

			return preg_replace('/^(?:[\\t ]*(?:-!)+)/', '', $param);
		}

		foreach ($param as $key => $value) {
			if (is_string($value)) {
				$return[$key] = preg_replace('/^(?:[\\t ]*(?:-!)+)/', '', $value);
			} else {
				foreach ($value as $array => $string) {
					$return[$key][$array] = self::stripEscape($string);
				}
			}
		}
		return $return;
	}

/**
 * Instructs the router to parse out file extensions from the URL. For example,
 * http://example.com/posts.rss would yield an file extension of "rss".
 * The file extension itself is made available in the controller as
 * self::$params['url']['ext'], and is used by the RequestHandler component to
 * automatically switch to alternate layouts and templates, and load helpers
 * corresponding to the given content, i.e. RssHelper.
 *
 * A list of valid extension can be passed to this method, i.e. Router::parseExtensions('rss', 'xml');
 * If no parameters are given, anything after the first . (dot) after the last / in the URL will be
 * parsed, excluding querystring parameters (i.e. ?q=...).
 *
 * @access public
 * @return void
 * @static
 */
	public static function parseExtensions() {
		self::$__parseExtensions = true;
		if (func_num_args() > 0) {
			self::$__validExtensions = func_get_args();
		}
	}

/**
 * Takes an passed params and converts it to args
 *
 * @param array $params
 * @return array Array containing passed and named parameters
 * @access public
 * @static
 */
	public static function getArgs($args, $options = array()) {
		$pass = $named = array();
		$args = explode('/', $args);

		$greedy = self::$named['greedy'];
		if (isset($options['greedy'])) {
			$greedy = $options['greedy'];
		}
		$context = array();
		if (isset($options['context'])) {
			$context = $options['context'];
		}
		$rules = self::$named['rules'];
		if (isset($options['named'])) {
			$greedy = isset($options['greedy']) && $options['greedy'] === true;
			foreach ((array)$options['named'] as $key => $val) {
				if (is_numeric($key)) {
					$rules[$val] = true;
					continue;
				}
				$rules[$key] = $val;
			}
		}

		foreach ($args as $param) {
			if (empty($param) && $param !== '0' && $param !== 0) {
				continue;
			}
			$param = self::stripEscape($param);

			$separatorIsPresent = strpos($param, self::$named['separator']) !== false;
			if ((!isset($options['named']) || !empty($options['named'])) && $separatorIsPresent) {
				list($key, $val) = explode(self::$named['separator'], $param, 2);
				$hasRule = isset($rules[$key]);
				$passIt = (!$hasRule && !$greedy) || ($hasRule && !self::matchNamed($key, $val, $rules[$key], $context));
				if ($passIt) {
					$pass[] = $param;
				} else {
					$named[$key] = $val;
				}
			} else {
				$pass[] = $param;
			}
		}
		return compact('pass', 'named');
	}
}
?>