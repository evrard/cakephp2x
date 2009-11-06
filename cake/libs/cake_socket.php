<?php
/**
 * Cake Socket connection class.
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
 * @since         CakePHP(tm) v 1.2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
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
	public $description = 'Remote DataSource Network Socket Interface';

/**
 * Base configuration settings for the socket connection
 *
 * @var array
 * @access protected
 */
	protected $_baseConfig = array(
		'persistent' => false,
		'host'       => 'localhost',
		'protocol'   => 'tcp',
		'port'       => 80,
		'timeout'    => 30
	);

/**
 * Configuration settings for the socket connection
 *
 * @var array
 * @access private
 */
	private $__config = array();

/**
 * Reference to socket connection resource
 *
 * @var resource
 * @access private
 */
	private $__connection = null;

/**
 * This boolean contains the current state of the CakeSocket class
 *
 * @var boolean
 * @access private
 */
	private $__connected = false;

/**
 * This variable contains an array with the last error number (num) and string (str)
 *
 * @var array
 * @access public
 */
	private $__lastError = array();

/**
 * Constructor.
 *
 * @param array $config Socket configuration, which will be merged with the base configuration
 */
	public function __construct($config = array()) {
		parent::__construct();

		$this->__config = array_merge($this->_baseConfig, $config);
		if (!is_numeric($this->__config['protocol'])) {
			$this->__config['protocol'] = getprotobyname($this->__config['protocol']);
		}
	}

/**
 * Connect the socket to the given host and port.
 *
 * @return boolean Success
 * @access public
 */
	public function connect() {
		if ($this->__connection != null) {
			$this->disconnect();
		}

		$scheme = null;
		if (isset($this->__config['request']) && $this->__config['request']['uri']['scheme'] == 'https') {
			$scheme = 'ssl://';
		}

		if ($this->__config['persistent'] == true) {
			$tmp = null;
			//TODO: Remove Error Suppression
			$this->__connection = @pfsockopen($scheme.$this->__config['host'], $this->__config['port'], $errNum, $errStr, $this->__config['timeout']);
		} else {
			//TODO: Remove Error Suppression
			$this->__connection = @fsockopen($scheme.$this->__config['host'], $this->__config['port'], $errNum, $errStr, $this->__config['timeout']);
		}

		if (!empty($errNum) || !empty($errStr)) {
			$this->setLastError($errStr, $errNum);
		}

		if (is_resource($this->__connection)) {
			stream_set_timeout($this->__connection, $this->__config['timeout']);
			return true;
		}
		return false;
	}

/**
 * Get the host name of the current connection.
 *
 * @return string Host name
 * @access public
 */
	public function host() {
		if (Validation::ip($this->__config['host'])) {
			return gethostbyaddr($this->__config['host']);
		} else {
			return gethostbyaddr($this->address());
		}
	}

/**
 * Return the connection status
 * 
 * @return boolean
 * @access public
 */
	public function isConnected() {
		return is_resource($this->__connection);
	}
/**
 * Get the IP address of the current connection.
 *
 * @return string IP address
 * @access public
 */
	public function address() {
		if (Validation::ip($this->__config['host'])) {
			return $this->__config['host'];
		} else {
			return gethostbyname($this->__config['host']);
		}
	}

/**
 * Get all IP addresses associated with the current connection.
 *
 * @return array IP addresses
 * @access public
 */
	public function addresses() {
		if (Validation::ip($this->__config['host'])) {
			return array($this->__config['host']);
		} else {
			return gethostbynamel($this->__config['host']);
		}
	}

/**
 * Get the last error as a string.
 *
 * @return string Last error
 * @access public
 */
	public function lastError() {
		if (!empty($this->__lastError)) {
			return $this->__lastError['num'].': '.$this->__lastError['str'];
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
	public function setLastError($errNum, $errStr) {
		$this->__lastError = array('num' => $errNum, 'str' => $errStr);
	}

/**
 * Write data to the socket.
 *
 * @param string $data The data to write to the socket
 * @return boolean Success
 * @access public
 */
	public function write($data) {
		if (!$this->isConnected()) {
			if (!$this->connect()) {
				return false;
			}
		}

		return fwrite($this->__connection, $data, strlen($data));
	}
/**
 * Read data from the socket. Returns false if no data is available or no connection could be
 * established.
 *
 * @param integer $length Optional buffer length to read; defaults to 1024
 * @return mixed Socket data
 * @access public
 */
	public function read($length = 1024) {
		if (!$this->isConnected()) {
			if (!$this->connect()) {
				return false;
			}
		}

		if (!feof($this->__connection)) {
			$buffer = fread($this->__connection, $length);
			$info = stream_get_meta_data($this->__connection);
			if ($info['timed_out']) {
				$this->setLastError(E_WARNING, __('Connection timed out', true));
				return false;
			}
			return $buffer;
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
	public function abort() {
		return false;
	}

/**
 * Disconnect the socket from the current connection.
 *
 * @return boolean Success
 * @access public
 */
	public function disconnect() {
		if (!$this->isConnected()) {
			return true;
		}
		
		$success = true;
		try {
			fclose($this->__connection);
		} catch (Exception $e) {
			$success = false;
			$this->setLastError(0, $e->getMessage());
		}
		

		if (!$this->isConnected()) {
			$this->__connection = null;
		}
		return !$this->isConnected();
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