<?php
/**
 * Short description for file.
 *
 * Long description for file
 *
 * PHP Version 5.x
 *
 * CakePHP(tm) Tests <https://trac.cakephp.org/wiki/Developement/TestSuite>
 * Copyright 2005-2009, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The Open Group Test Suite License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2009, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          https://trac.cakephp.org/wiki/Developement/TestSuite CakePHP(tm) Tests
 * @package       cake
 * @subpackage    cake.tests.fixtures
 * @since         CakePHP(tm) v 1.2.0.7198
 * @license       http://www.opensource.org/licenses/opengroup.php The Open Group Test Suite License
 */

/**
 * Short description for class.
 *
 * @package       cake
 * @subpackage    cake.tests.fixtures
 */
class CdFixture extends CakeTestFixture {

/**
 * name property
 *
 * @var string 'Cd'
 * @access public
 */
	var $name = 'Cd';

/**
 * fields property
 *
 * @var array
 * @access public
 */
	var $fields = array(
		'id' => array('type' => 'integer', 'key' => 'primary'),
		'title' => array('type' => 'string', 'length' =>  255),
		'artist' => array('type' => 'string', 'length' => 255, 'null' => true),
		'genre' => array('type' => 'string', 'length' => 255, 'null' => true)
	);

/**
 * records property
 *
 * @var array
 * @access public
 */
	var $records = array(
		array('id' => 1, 'title' => 'Grace', 'artist' => 'Jeff Buckley', 'genre' => 'awesome')
	);
}
?>