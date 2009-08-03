<?php
/**
 * Short description for file.
 *
 * This file includes js vendor-files from /vendor/ directory if they need to
 * be accessible to the public.
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
 * @subpackage    cake.app.webroot.js
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */

/**
 * Enter description here...
 */
if (isset($_GET['file'])) {
	$file = $_GET['file'];
	$pos = strpos($file, '..');
	if ($pos === false) {
		if (is_file('../../vendors/javascript/'.$file) && (preg_match('/(\/.+)\\.js/', $file))) {
			readfile('../../vendors/javascript/'.$file);
			return;
		}
	}
}
header('HTTP/1.1 404 Not Found');
?>