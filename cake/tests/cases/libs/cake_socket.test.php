<?php
/**
 * SocketTest file
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
 * @subpackage    cake.tests.cases.libs
 * @since         CakePHP(tm) v 1.2.0.4206
 * @license       http://www.opensource.org/licenses/opengroup.php The Open Group Test Suite License
 */
App::import('Core', 'CakeSocket');

/**
 * SocketTest class
 *
 * @package       cake
 * @subpackage    cake.tests.cases.libs
 */
class CakeSocketTest extends CakeTestCase {

/**
 * setUp method
 *
 * @access public
 * @return void
 */
	function setUp() {
		$this->Socket = new CakeSocket();
	}

/**
 * tearDown method
 *
 * @access public
 * @return void
 */
	function tearDown() {
		unset($this->Socket);
	}

/**
 * testSocketConnection method
 *
 * @access public
 * @return void
 */
	function testSocketConnection() {
		$this->assertFalse($this->Socket->isConnected());
		$this->Socket->disconnect();
		$this->assertFalse($this->Socket->isConnected());
		$this->Socket->connect();
		$this->assertTrue($this->Socket->isConnected());
		$this->Socket->connect();
		$this->assertTrue($this->Socket->isConnected());
		$this->Socket->disconnect();
		$this->assertFalse($this->Socket->isConnected());
		$config = array('persistent' => true);
		$this->Socket = new CakeSocket($config);
		$this->Socket->connect();
		$this->assertTrue($this->Socket->isConnected());
	}

/**
 * testSocketHost method
 *
 * @access public
 * @return void
 */
	function testSocketHost() {
		$this->Socket = new CakeSocket();
		$this->Socket->connect();
		$this->assertEqual($this->Socket->address(), '127.0.0.1');
		$this->assertEqual(gethostbyaddr('127.0.0.1'), $this->Socket->host());
		$this->assertEqual($this->Socket->lastError(), null);
		$this->assertTrue(in_array('127.0.0.1', $this->Socket->addresses()));

		$this->Socket = new CakeSocket(array('host' => '127.0.0.1'));
		$this->Socket->connect();
		$this->assertEqual($this->Socket->address(), '127.0.0.1');
		$this->assertEqual(gethostbyaddr('127.0.0.1'), $this->Socket->host());
		$this->assertEqual($this->Socket->lastError(), null);
		$this->assertTrue(in_array('127.0.0.1', $this->Socket->addresses()));
	}

/**
 * testSocketWriting method
 *
 * @access public
 * @return void
 */
	function testSocketWriting() {
		$request = "GET / HTTP/1.1\r\nConnection: close\r\n\r\n";
		$this->assertTrue($this->Socket->write($request));
	}

/**
 * testSocketReading method
 *
 * @access public
 * @return void
 */
	function testSocketReading() {
		$this->Socket = new CakeSocket(array('timeout' => 5));
		$this->Socket->connect();
		$this->assertEqual($this->Socket->read(26), null);

		$config = array('host' => 'www.cakephp.org', 'timeout' => 1);
		$this->Socket = new CakeSocket($config);
		$this->assertTrue($this->Socket->connect());
		$this->assertFalse($this->Socket->read(1024 * 1024));
		$this->assertEqual($this->Socket->lastError(), '2: ' . __('Connection timed out', true));

		$config = array('host' => 'www.cakephp.org', 'timeout' => 30);
		$this->Socket = new CakeSocket($config);
		$this->assertTrue($this->Socket->connect());
		$this->assertEqual($this->Socket->read(26), null);
		$this->assertEqual($this->Socket->lastError(), null);
	}

/**
 * testLastError method
 *
 * @access public
 * @return void
 */
	function testLastError() {
		$this->Socket = new CakeSocket();
		$this->Socket->setLastError(4, 'some error here');
		$this->assertEqual($this->Socket->lastError(), '4: some error here');
	}

/**
 * testReset method
 *
 * @access public
 * @return void
 */
	function testReset() {
		$config = array(
			'persistent'	=> true,
			'host'			=> '127.0.0.1',
			'protocol'		=> 'udp',
			'port'			=> 80,
			'timeout'		=> 20
		);
		
		$FirstSocket = new CakeSocket($config);
		$SecondSocket = new CakeSocket($config); 
		
		$this->assertEqual($FirstSocket, $SecondSocket);
		
		$FirstSocket->reset();
		$this->assertNotEqual($FirstSocket, $SecondSocket);
	}
}
?>