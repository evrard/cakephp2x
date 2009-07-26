<?php
/**
 * Caching for CakePHP.
 *
 *
 * PHP Version 5.x
 *
 * CakePHP(tm) : Rapid Development Framework (http://www.cakephp.org)
 * Copyright 2005-2009, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright     Copyright 2005-2009, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.cake.libs
 * @since         CakePHP(tm) v 1.2.0.4933
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Caching for CakePHP.
 *
 * @package       cake
 * @subpackage    cake.cake.libs
 */
class Cache extends Object {
/**
 * Cache engine to use
 *
 * @var CacheEngine
 * @access protected
 */
	private static $_Engine = null;
/**
 * Cache configuration stack
 *
 * @var array
 * @access private
 */
	private static $__config = array();
/**
 * Holds name of the current configuration being used
 *
 * @var array
 * @access private
 */
	private static $__name = 'default';
/**
 * whether to reset the settings with the next call to self::set();
 *
 * @var array
 * @access private
 */
	private static $__reset = false;
/**
 * Returns a singleton instance
 *
 * @return object
 * @access public
 * @static
 */
/*
	private function &getInstance() {
		static $instance = array();
		if (!$instance) {
			$instance[0] = new Cache();
		}
		return $instance[0];
	}
*/
/**
 * Tries to find and include a file for a cache engine and returns object instance
 *
 * @param $name	Name of the engine (without 'Engine')
 * @return mixed $engine object or null
 * @access private
 */
	private static function __loadEngine($name) {
		if (!class_exists($name . 'Engine')) {
			require LIBS . DS . 'cache' . DS . strtolower($name) . '.php';
		}
		return true;
	}
/**
 * Set the cache configuration to use
 *
 * @see app/config/core.php for configuration settings
 * @param string $name Name of the configuration
 * @param array $settings Optional associative array of settings passed to the engine
 * @return array(engine, settings) on success, false on failure
 * @access public
 * @static
 */
	public static function config($name = null, $settings = array()) {
		if (is_array($name)) {
			$settings = $name;
		}

		if ($name === null || !is_string($name)) {
			$name = self::$__name;
		}

		$current = array();
		if (isset(self::$__config[$name])) {
			$current = self::$__config[$name];
		}

		if (!empty($settings)) {
			self::$__name = null;
			self::$__config[$name] = array_merge($current, $settings);
		}

		if (empty(self::$__config[$name]['engine'])) {
			return false;
		}

		self::$__name = $name;
		$engine = self::$__config[$name]['engine'];

		if (!self::isInitialized($engine)) {
			if (self::engine($engine, self::$__config[$name]) === false) {
				return false;
			}
			$settings = self::$__config[$name] = self::settings($engine);
		} else {
			$settings = self::$__config[$name] = self::set(self::$__config[$name]);
		}
		return compact('engine', 'settings');
	}
/**
 * Set the cache engine to use or modify settings for one instance
 *
 * @param string $name Name of the engine (without 'Engine')
 * @param array $settings Optional associative array of settings passed to the engine
 * @return boolean True on success, false on failure
 * @access public
 * @static
 */
	public static function engine($name = 'File', $settings = array()) {
		$cacheClass = $name . 'Engine';

		if (!isset(self::$_Engine[$name])) {
			if (self::__loadEngine($name) === false) {
				return false;
			}
			self::$_Engine[$name] = new $cacheClass();
		}

		if (self::$_Engine[$name]->init($settings)) {
			if (time() % self::$_Engine[$name]->settings('probability') === 0) {
				self::$_Engine[$name]->gc();
			}
			return true;
		}
		self::$_Engine[$name] = null;
		return false;
	}
/**
 * Temporarily change settings to current config options. if no params are passed, resets settings if needed
 * Cache::write() will reset the configuration changes made
 *
 * @param mixed $settings Optional string for simple name-value pair or array
 * @param string $value Optional for a simple name-value pair
 * @return array of settings
 * @access public
 * @static
 */
	public static function set($settings = array(), $value = null) {
		if (!isset(self::$__config[self::$__name])) {
			return false;
		}

		$engine = self::$__config[self::$__name]['engine'];

		if (!empty($settings)) {
			self::$__reset = true;
		}

		if (self::$__reset === true) {
			if (empty($settings)) {
				self::$__reset = false;
				$settings = self::$__config[self::$__name];
			} else {
				if (is_string($settings) && $value !== null) {
					$settings = array($settings => $value);
				}
				$settings = array_merge(self::$__config[self::$__name], $settings);
			}
			self::$_Engine[$engine]->init($settings);
		}

		return self::settings($engine);
	}
/**
 * Garbage collection
 *
 * Permanently remove all expired and deleted data
 *
 * @return void
 * @access public
 * @static
 */
	public static function gc() {
		$config = self::config();
		self::$_Engine[$config['engine']]->gc();
	}
/**
 * Write data for key into cache
 *
 * @param string $key Identifier for the data
 * @param mixed $value Data to be cached - anything except a resource
 * @param string $config Optional - string configuration name
 * @return boolean True if the data was successfully cached, false on failure
 * @access public
 * @static
 */
	public static function write($key, $value, $config = null) {
		if (is_array($config)) {
			extract($config);
		} else if ($config && (is_numeric($config) || is_numeric($config[0]) || (isset($config[1]) && is_numeric($config[1])))) {
			$config = null;
		}

		if ($config && isset(self::$__config[$config])) {
			$settings = self::set(self::$__config[$config]);
		} else {
			$settings = self::settings();
		}

		if (empty($settings)) {
			return null;
		}
		extract($settings);

		if (!self::isInitialized($engine)) {
			return false;
		}

		if (!$key = self::$_Engine[$engine]->key($key)) {
			return false;
		}

		if (is_resource($value)) {
			return false;
		}

		if ($duration < 1) {
			return false;
		}

		$success = self::$_Engine[$engine]->write($settings['prefix'] . $key, $value, $duration);
		$settings = self::set();
		return $success;
	}
/**
 * Read a key from the cache
 *
 * @param string $key Identifier for the data
 * @param string $config name of the configuration to use
 * @return mixed The cached data, or false if the data doesn't exist, has expired, or if there was an error fetching it
 * @access public
 * @static
 */
	public static function read($key, $config = null) {
		if (isset(self::$__config[$config])) {
			$settings = self::set(self::$__config[$config]);
		} else {
			$settings = self::settings();
		}

		if (empty($settings)) {
			return null;
		}
		extract($settings);

		if (!self::isInitialized($engine)) {
			return false;
		}
		if (!$key = self::$_Engine[$engine]->key($key)) {
			return false;
		}
		$success = self::$_Engine[$engine]->read($settings['prefix'] . $key);

		if ($config !== null && $config !== self::$__name) {
			$settings = self::set();
		}
		return $success;
	}
/**
 * Delete a key from the cache
 *
 * @param string $key Identifier for the data
 * @param string $config name of the configuration to use
 * @return boolean True if the value was succesfully deleted, false if it didn't exist or couldn't be removed
 * @access public
 * @static
 */
	public static function delete($key, $config = null) {
		if (isset(self::$__config[$config])) {
			$settings = self::set(self::$__config[$config]);
		} else {
			$settings = self::settings();
		}

		if (empty($settings)) {
			return null;
		}
		extract($settings);

		if (!self::isInitialized($engine)) {
			return false;
		}

		if (!$key = self::$_Engine[$engine]->key($key)) {
			return false;
		}

		$success = self::$_Engine[$engine]->delete($settings['prefix'] . $key);
		$settings = self::set();
		return $success;
	}
/**
 * Delete all keys from the cache
 *
 * @param boolean $check if true will check expiration, otherwise delete all
 * @param string $config name of the configuration to use
 * @return boolean True if the cache was succesfully cleared, false otherwise
 * @access public
 * @static
 */
	public static function clear($check = false, $config = null) {
		if (isset(self::$__config[$config])) {
			$settings = self::set(self::$__config[$config]);
		} else {
			$settings = self::settings();
		}

		if (empty($settings)) {
			return null;
		}
		extract($settings);

		if (isset($engine) && !self::isInitialized($engine)) {
			return false;
		}
		$success = self::$_Engine[$engine]->clear($check);
		$settings = self::set();
		return $success;
	}
/**
 * Check if Cache has initialized a working storage engine
 *
 * @param string $engine Name of the engine
 * @param string $config Name of the configuration setting
 * @return bool
 * @access public
 * @static
 */
	public static function isInitialized($engine = null) {
		if (Configure::read('Cache.disable')) {
			return false;
		}
		if (!$engine && isset(self::$__config[self::$__name]['engine'])) {
			$engine = self::$__config[self::$__name]['engine'];
		}
		return isset(self::$_Engine[$engine]);
	}

/**
 * Return the settings for current cache engine
 *
 * @param string $engine Name of the engine
 * @return array list of settings for this engine
 * @access public
 * @static
 */
	public static function settings($engine = null) {
		if (!$engine && isset(self::$__config[self::$__name]['engine'])) {
			$engine = self::$__config[self::$__name]['engine'];
		}

		if (isset(self::$_Engine[$engine]) && !is_null(self::$_Engine[$engine])) {
			return self::$_Engine[$engine]->settings();
		}
		return array();
	}
}
/**
 * Storage engine for CakePHP caching
 *
 * @package       cake
 * @subpackage    cake.cake.libs
 */
class CacheEngine extends Object {
/**
 * settings of current engine instance
 *
 * @var int
 * @access public
 */
	protected $settings = array();
/**
 * Iitialize the cache engine
 *
 * Called automatically by the cache frontend
 *
 * @param array $params Associative array of parameters for the engine
 * @return boolean True if the engine has been succesfully initialized, false if not
 * @access public
 */
	protected function init($settings = array()) {
		$this->settings = array_merge(array('prefix' => 'cake_', 'duration'=> 3600, 'probability'=> 100), $this->settings, $settings);
		if (!is_numeric($this->settings['duration'])) {
			$this->settings['duration'] = strtotime($this->settings['duration']) - time();
		}
		return true;
	}
/**
 * Garbage collection
 *
 * Permanently remove all expired and deleted data
 *
 * @access public
 */
	public function gc() {
	}
/**
 * Write value for a key into cache
 *
 * @param string $key Identifier for the data
 * @param mixed $value Data to be cached
 * @param mixed $duration How long to cache the data, in seconds
 * @return boolean True if the data was succesfully cached, false on failure
 * @access public
 */
	public function write($key, &$value, $duration) {
		trigger_error(sprintf(__('Method write() not implemented in %s', true), get_class($this)), E_USER_ERROR);
	}
/**
 * Read a key from the cache
 *
 * @param string $key Identifier for the data
 * @return mixed The cached data, or false if the data doesn't exist, has expired, or if there was an error fetching it
 * @access public
 */
	public function read($key) {
		trigger_error(sprintf(__('Method read() not implemented in %s', true), get_class($this)), E_USER_ERROR);
	}
/**
 * Delete a key from the cache
 *
 * @param string $key Identifier for the data
 * @return boolean True if the value was succesfully deleted, false if it didn't exist or couldn't be removed
 * @access public
 */
	public function delete($key) {
	}
/**
 * Delete all keys from the cache
 *
 * @param boolean $check if true will check expiration, otherwise delete all
 * @return boolean True if the cache was succesfully cleared, false otherwise
 * @access public
 */
	public function clear($check) {
	}
/**
 * Cache Engine settings
 *
 * @return array settings
 * @access public
 */
	public function settings($key = null) {
		if (empty($key) || !array_key_exists($this->settings[$key])) {
			return $this->settings;
		}
		return $this->settings[$key];
	}
/**
 * generates a safe key
 *
 * @param string $key the key passed over
 * @return mixed string $key or false
 * @access public
 */
	public function key($key) {
		if (empty($key)) {
			return false;
		}
		return Inflector::underscore(str_replace(array(DS, '/', '.'), '_', strval($key)));
	}
}
?>