<?php
/**
 * Class collections.
 *
 * A repository for class objects, each registered with a key.
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
 * @since         CakePHP(tm) v 0.9.2
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */

/**
 * Class Collections.
 *
 * A repository for class objects, each registered with a key.
 * If you try to add an object with the same key twice, nothing will come of it.
 * If you need a second instance of an object, give it another key.
 *
 * @package       cake
 * @subpackage    cake.cake.libs
 */
final class ClassRegistry {

/**
 * Names of classes with their objects.
 *
 * @var array
 * @access private
 */
	private static $__objects = array();

/**
 * Names of class names mapped to the object in the registry.
 *
 * @var array
 * @access private
 */
	private static $__map = array();

/**
 * Default constructor parameter settings, indexed by type
 *
 * @var array
 * @access private
 */
	private static $__config = array();

/**
 * Loads a class, registers the object in the registry and returns instance of the object.
 *
 * Examples
 * Simple Use: Get a Post model instance ```ClassRegistry::init('Post');```
 *
 * Exapanded: ```array('class' => 'ClassName', 'alias' => 'AliasNameStoredInTheRegistry', 'type' => 'TypeOfClass');```
 *
 * Model Classes can accept optional ```array('id' => $id, 'table' => $table, 'ds' => $ds, 'alias' => $alias);```
 *
 * When $class is a numeric keyed array, multiple class instances will be stored in the registry,
 *  no instance of the object will be returned
 * {{{
 * array(
 *		array('class' => 'ClassName', 'alias' => 'AliasNameStoredInTheRegistry', 'type' => 'TypeOfClass'),
 *		array('class' => 'ClassName', 'alias' => 'AliasNameStoredInTheRegistry', 'type' => 'TypeOfClass'),
 *		array('class' => 'ClassName', 'alias' => 'AliasNameStoredInTheRegistry', 'type' => 'TypeOfClass')
 * );
 * }}}
 * @param mixed $class as a string or a single key => value array instance will be created,
 *  stored in the registry and returned.
 * @param string $type TypeOfClass
 * @return object instance of ClassName
 * @access public
 * @static
 */
	public static function &init($class, $type = null) {
		$id = $false = false;
		$true = true;

		if (!$type) {
			$type = 'Model';
		}

		if (is_array($class)) {
			$objects = $class;
			if (!isset($class[0])) {
				$objects = array($class);
			}
		} else {
			$objects = array(array('class' => $class));
		}
		$defaults = isset(self::$__config[$type]) ? self::$__config[$type] : array();
		$count = count($objects);

		foreach ($objects as $key => $settings) {
			if (is_array($settings)) {
				$plugin = $pluginPath = null;
				$settings = array_merge($defaults, $settings);
				$class = $settings['class'];

				if (strpos($class, '.') !== false) {
					list($plugin, $class) = explode('.', $class);
					$pluginPath = $plugin . '.';
				}

				if (empty($settings['alias'])) {
					$settings['alias'] = $class;
				}
				$alias = $settings['alias'];

				if ($model = self::__duplicate($alias, $class)) {
					self::map($alias, $class);
					return $model;
				}

				if (class_exists($class) || App::import($type, $pluginPath . $class)) {
					${$class} = new $class($settings);
				} elseif ($type === 'Model') {
					if ($plugin && class_exists($plugin . 'AppModel')) {
						$appModel = $plugin . 'AppModel';
					} else {
						$appModel = 'AppModel';
					}
					$settings['name'] = $class;
					${$class} = new $appModel($settings);
				}

				if (!isset(${$class})) {
					trigger_error(sprintf(__('(ClassRegistry::init() could not create instance of %1$s class %2$s ', true), $class, $type), E_USER_WARNING);
					return $false;
				}

				if ($type !== 'Model') {
					self::addObject($alias, ${$class});
				} else {
					self::map($alias, $class);
				}
			} elseif (is_numeric($settings)) {
				trigger_error(__('(ClassRegistry::init() Attempted to create instance of a class with a numeric name', true), E_USER_WARNING);
				return $false;
			}
		}

		if ($count > 1) {
			return $true;
		}
		return ${$class};
	}

/**
 * Add $object to the registry, associating it with the name $key.
 *
 * @param string $key	Key for the object in registry
 * @param mixed $object	Object to store
 * @return boolean True if the object was written, false if $key already exists
 * @access public
 * @static
 */
	public static function addObject($key, &$object) {
		$key = Inflector::underscore($key);
		if (!isset(self::$__objects[$key])) {
			self::$__objects[$key] = $object;
			return true;
		}
		return false;
	}

/**
 * Remove object which corresponds to given key.
 *
 * @param string $key	Key of object to remove from registry
 * @return void
 * @access public
 * @static
 */
	public static function removeObject($key) {
		$key = Inflector::underscore($key);
		if (isset(self::$__objects[$key])) {
			unset(self::$__objects[$key]);
		}
	}

/**
 * Returns true if given key is present in the ClassRegistry.
 *
 * @param string $key Key to look for
 * @return boolean true if key exists in registry, false otherwise
 * @access public
 * @static
 */
	public static function isKeySet($key) {
		$key = Inflector::underscore($key);
		if (isset(self::$__objects[$key])) {
			return true;
		} elseif (isset(self::$__map[$key])) {
			return true;
		}
		return false;
	}

/**
 * Get all keys from the registry.
 *
 * @return array Set of keys stored in registry
 * @access public
 * @static
 */
	public static function keys() {
		return array_keys(self::$__objects);
	}

/**
 * Return object which corresponds to given key.
 *
 * @param string $key Key of object to look for
 * @return mixed Object stored in registry
 * @access public
 * @static
 */
	public static function &getObject($key) {
		$key = Inflector::underscore($key);
		if (isset(self::$__objects[$key])) {
			return self::$__objects[$key];
		} else {
			$key = self::__getMap($key);
			if (isset(self::$__objects[$key])) {
				return self::$__objects[$key];
			}
		}
		return false;
	}

/**
 * Sets the default constructor parameter for an object type
 *
 * @param string $type Type of object.  If this parameter is omitted, defaults to "Model"
 * @param array $param The parameter that will be passed to object constructors when objects
 *                      of $type are created
 * @return mixed Void if $param is being set.  Otherwise, if only $type is passed, returns
 *               the previously-set value of $param, or null if not set.
 * @access public
 * @static
 */
	public static function config($type, $param = array()) {
		if (empty($param) && is_array($type)) {
			$param = $type;
			$type = 'Model';
		} elseif (is_null($param)) {
			unset(self::$__config[$type]);
		} elseif (empty($param) && is_string($type)) {
			return isset(self::$__config[$type]) ? self::$__config[$type] : null;
		}
		self::$__config[$type] = $param;
	}

/**
 * Add a key name pair to the registry to map name to class in the registry.
 *
 * @param string $key Key to include in map
 * @param string $name Key that is being mapped
 * @access public
 * @static
 */
	public static function map($key, $name) {
		$key = Inflector::underscore($key);
		$name = Inflector::underscore($name);
		if (!isset(self::$__map[$key])) {
			self::$__map[$key] = $name;
		}
	}

/**
 * Get all keys from the map in the registry.
 *
 * @return array Keys of registry's map
 * @access public
 * @static
 */
	public static function mapKeys() {
		return array_keys(self::$__map);
	}

/**
 * Flushes all objects from the ClassRegistry.
 *
 * @return void
 * @access public
 * @static
 */
	public static function flush() {
		self::$__objects = array();
		self::$__map = array();
	}
/**
 * Checks to see if $alias is a duplicate $class Object
 *
 * @param string $alias
 * @param string $class
 * @return boolean
 * @access private
 * @static
 */
	private static function __duplicate($alias,  $class) {
		$duplicate = false;
		if (self::isKeySet($alias)) {
			$model = self::getObject($alias);
			if (is_object($model) && (is_a($model, $class) || $model->alias === $class)) {
				$duplicate = $model;
			}
			unset($model);
		}
		return $duplicate;
	}

/**
 * Return the name of a class in the registry.
 *
 * @param string $key Key to find in map
 * @return string Mapped value
 * @access private
 * @static
 */
	private static function __getMap($key) {
		if (isset(self::$__map[$key])) {
			return self::$__map[$key];
		}
	}
}
?>