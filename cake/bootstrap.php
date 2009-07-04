<?php
/**
 * Basic Cake functionality.
 *
 * Handles loading of core files needed on every request
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
 * @subpackage    cake.cake
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */
if (!defined('PHP5')) {
	define('PHP5', (PHP_VERSION >= 5));
}
require CORE_PATH . 'cake' . DS . 'basics.php';
$TIME_START = microtime(true);
require CORE_PATH . 'cake' . DS . 'config' . DS . 'paths.php';
require LIBS . 'object.php';
require LIBS . 'inflector.php';
require LIBS . 'configure.php';
require LIBS . 'set.php';
require LIBS . 'cache.php';
Configure::getInstance();
require CAKE . 'dispatcher.php';
?>
