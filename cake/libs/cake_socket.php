<?php
/**
 * Cake Socket connection class.
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
 * @since         CakePHP(tm) v 1.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */
App::import('Core', 'Validation');
/**
 * Cake network socket connection class.
 *
 * Core base class for network communication.
 *
 * @package       cake
 * @subpackage    cake.cake.libs
 */
class CakeSocket extends Object {
/**
 * Object description
 *
 * @var string
 * @access public
 */
	private $description = 'Remote DataSource Network Socket Interface';
/**
 * Base configuration settings for the socket connection
 *
 * @var array
 * @access protected
 */
	private $_baseConfig = array(
		'persistent'	=> false,
		'host'			=> 'localhost',
		'protocol'		=> 'tcp',
		'port'			=> 80,
		'timeout'		=> 30
	);
/**
 * Configuration settings for the socket connection
 *
 * @var array
 * @access public
 */
	private $config = array();
/**
 * Reference to socket connection resource
 *
 * @var resource
 * @access public
 */
	private $connection = null;
/**
 * This boolean contains the current state of the CakeSocket class
 *
 * @var boolean
 * @access public
 */
	private $connected = false;
/**
 * This variable contains an array with the last error number (num) and string (str)
 *
 * @var array
 * @access public
 */
	private $lastError = array();
/**
 * Constructor.
 *
 * @param array $config Socket configuration, which will be merged with the base configuration
 */
	public function __construct($config = array()) {
		parent::__construct();

		$this->config = array_merge($this->_baseConfig, $config);
		if (!is_numeric($this->config['protocol'])) {
			$this->config['protocol'] = getprotobyname($this->config['protocol']);
		}
	}
/**
 * Connect the socket to the given host and port.
 *
 * @return boolean Success
 * @access public
 */
	private function connect() {
		if ($this->connection != null) {
			$this->disconnect();
		}

		$scheme = null;
		if (isset($this->config['request']) && $this->config['request']['uri']['scheme'] == 'https') {
			$scheme = 'ssl://';
		}

		if ($this->config['persistent'] == true) {
			$tmp = null;
			$this->connection = @pfsockopen($scheme.$this->config['host'], $this->config['port'], $errNum, $errStr, $this->config['timeout']);
		} else {
			$this->connection = @fsockopen($scheme.$this->config['host'], $this->config['port'], $errNum, $errStr, $this->config['timeout']);
		}

		if (!empty($errNum) || !empty($errStr)) {
			$this->setLastError($errStr, $errNum);
		}

		return $this->connected = is_resource($this->connection);
	}

/**
 * Get the host name of the current connection.
 *
 * @return string Host name
 * @access public
 */
	private function host() {
		if (Validation::ip($this->config['host'])) {
			return gethostbyaddr($this->config['host']);
		} else {
			return gethostbyaddr($this->address());
		}
	}
/**
 * Get the IP address of the current connection.
 *
 * @return string IP address
 * @access public
 */
	private function address() {
		if (Validation::ip($this->config['host'])) {
			return $this->config['host'];
		} else {
			return gethostbyname($this->config['host']);
		}
	}
/**
 * Get all IP addresses associated with the current connection.
 *
 * @return array IP addresses
 * @access public
 */
	private function addresses() {
		if (Validation::ip($this->config['host'])) {
			return array($this->config['host']);
		} else {
			return gethostbynamel($this->config['host']);
		}
	}
/**
 * Get the last error as a string.
 *
 * @return string Last error
 * @access public
 */
	private function lastError() {
		if (!empty($this->lastError)) {
			return $this->lastError['num'].': '.$this->lastError['str'];
		} else {
			return null;
		}
	}
/**
 * Set the last error.
 *
 * @param integer $errNum Error code
 * @param string $errStr Error string
 * @access public
 */
	private function setLastError($errNum, $errStr) {
		$this->lastError = array('num' => $errNum, 'str' => $errStr);
	}
/**
 * Write data to the socket.
 *
 * @param string $data The data to write to the socket
 * @return boolean Success
 * @access public
 */
	private function write($data) {
		if (!$this->connected) {
			if (!$this->connect()) {
				return false;
			}
		}

		return fwrite($this->connection, $data, strlen($data));
	}

/**
 * Read data from the socket. Returns false if no data is available or no connection could be
 * established.
 *
 * @param integer $length Optional buffer length to read; defaults to 1024
 * @return mixed Socket data
 * @access public
 */
	private function read($length = 1024) {
		if (!$this->connected) {
			if (!$this->connect()) {
				return false;
			}
		}

		if (!feof($this->connection)) {
			return fread($this->connection, $length);
		} else {
			return false;
		}
	}
/**
 * Abort socket operation.
 *
 * @return boolean Success
 * @access public
 */
	private function abort() {
	}
/**
 * Disconnect the socket from the current connection.
 *
 * @return boolean Success
 * @access public
 */
	public function disconnect() {
		if (!is_resource($this->connection)) {
			$this->connected = false;
			return true;
		}
		$this->connected = !fclose($this->connection);

		if (!$this->connected) {
			$this->connection = null;
		}
		return !$this->connected;
	}
/**
 * Destructor, used to disconnect from current connection.
 *
 * @access private
 */
	public function __destruct() {
		$this->disconnect();
	}
/**
 * Resets the state of this Socket instance to it's initial state (before Object::__construct got executed)
 *
 * @return boolean True on success
 * @access public
 */
	public function reset($state = null) {
		if (empty($state)) {
			static $initalState = array();
			if (empty($initalState)) {
				$initalState = get_class_vars(__CLASS__);
			}
			$state = $initalState;
		}

		foreach ($state as $property => $value) {
			$this->{$property} = $value;
		}
		return true;
	}
}
?>