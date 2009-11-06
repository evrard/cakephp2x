<?php
/**
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
 * @subpackage    cake.cake.libs.view.templates.errors
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */
?>
<h2><?php echo sprintf(__('Missing Method in %s'), $controller); ?></h2>
<p class="error">
	<strong><?php __('Error', false) ?>: </strong>
	<?php echo sprintf(__('The action %1$s is not defined in controller %2$s'), "<em>" . $action . "</em>", "<em>" . $controller . "</em>"); ?>
</p>
<p class="error">
	<strong><?php __('Error', false) ?>: </strong>
	<?php echo sprintf(__('Create %1$s%2$s in file: %3$s.'), "<em>" . $controller . "::</em>", "<em>" . $action . "()</em>", APP_DIR . DS . "controllers" . DS . Inflector::underscore($controller) . ".php"); ?>
</p>
<pre>
&lt;?php
class <?php echo $controller; ?> extends AppController {

	public $name = '<?php echo $controllerName; ?>';

<strong>
	private function <?php echo $action; ?>() {

	}
</strong>
}
?&gt;
</pre>
<p class="notice">
	<strong><?php __('Notice', flase) ?>: </strong>
	<?php echo sprintf(__('If you want to customize this error message, create %s.'), APP_DIR . DS . "views" . DS . "errors" . DS . "missing_action.ctp"); ?>
</p>