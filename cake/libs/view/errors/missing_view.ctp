<?php
/**
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
 * @subpackage    cake.cake.libs.view.templates.errors
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */
?>
<h2><?php __('Missing View', false); ?></h2>
<p class="error">
	<strong><?php __('Error', false); ?>: </strong>
	<?php echo sprintf(__('The view for %1$s%2$s was not found.'), '<em>' . $controller . 'Controller::</em>', '<em>'. $action . '()</em>'); ?>
</p>
<p class="error">
	<strong><?php __('Error', false); ?>: </strong>
	<?php echo sprintf(__('Confirm you have created the file: %s'), $file); ?>
</p>
<p class="notice">
	<strong><?php __('Notice', false); ?>: </strong>
	<?php echo sprintf(__('If you want to customize this error message, create %s'), APP_DIR . DS . 'views' . DS . 'errors' . DS . 'missing_view.ctp'); ?>
</p>