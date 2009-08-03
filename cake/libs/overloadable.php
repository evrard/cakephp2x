<?php
/**
 * Overload abstraction interface.  Merges differences between PHP4 and 5.
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
 * @since         CakePHP(tm) v 1.2
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */

/**
 * Overloadable class
 *
 * @package       cake
 * @subpackage    cake.cake.libs
 */
class Overloadable extends Object {

/**
 * Magic method handler.
 *
 * @param string $method Method name
 * @param array $params Parameters to send to method
 * @return mixed Return value from method
 * @access private
 */
	protected function __call($method, $params) {
		if (!method_exists($this, 'call__')) {
			trigger_error(sprintf(__('Magic method handler call__ not defined in %s', true), get_class($this)), E_USER_ERROR);
		}
		return $this->call__($method, $params);
	}
}

/**
 * Overloadable2 class
 *
 * @package       cake
 * @subpackage    cake.cake.libs
 */
class Overloadable2 extends Object {

/**
 * Magic method handler.
 *
 * @param string $method Method name
 * @param array $params Parameters to send to method
 * @return mixed Return value from method
 * @access protected
 */
	protected function __call($method, $params) {
		if (!method_exists($this, 'call__')) {
			trigger_error(sprintf(__('Magic method handler call__ not defined in %s', true), get_class($this)), E_USER_ERROR);
		}
		return $this->call__($method, $params);
	}

/**
 * Getter.
 *
 * @param mixed $name What to get
 * @param mixed $value Where to store returned value
 * @return boolean Success
 * @access protected
 */
	protected function __get($name) {
		return $this->get__($name);
	}

/**
 * Setter.
 *
 * @param mixed $name What to set
 * @param mixed $value Value to set
 * @return boolean Success
 * @access protected
 */
	protected function __set($name, $value) {
		return $this->set__($name, $value);
	}
}
?>