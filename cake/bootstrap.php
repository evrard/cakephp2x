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
if (!defined('E_DEPRECATED')) {
	define('E_DEPRECATED', 8192);
}
error_reporting(E_ALL & ~E_DEPRECATED);

require CORE_PATH . 'cake' . DS . 'basics.php';
$TIME_START = microtime(true);
require CORE_PATH . 'cake' . DS . 'config' . DS . 'paths.php';
require LIBS . 'object.php';
require LIBS . 'inflector.php';
require LIBS . 'configure.php';
require LIBS . 'set.php';
require LIBS . 'cache.php';
Configure::init();
require CAKE . 'dispatcher.php';
?>
