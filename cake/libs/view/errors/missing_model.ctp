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
<h2><?php __('Missing Model', false); ?></h2>
<p class="error">
	<strong><?php __('Error', false); ?>: </strong>
	<?php echo sprintf(__('<em>%s</em> could not be found.'), $model); ?>
</p>
<p class="error">
	<strong><?php __('Error', false); ?>: </strong>
	<?php echo sprintf(__('Create the class %s in file: %s'), '<em>' . $model . '</em>', APP_DIR . DS . 'models' . DS . Inflector::underscore($model) . '.php'); ?>
</p>
<pre>
&lt;?php
class <?php echo $model;?> extends AppModel {

	public $name = '<?php echo $model; ?>';

}
?&gt;
</pre>
<p class="notice">
	<strong><?php __('Notice', false); ?>: </strong>
	<?php echo sprintf(__('If you want to customize this error message, create %s'), APP_DIR . DS . 'views' . DS . 'errors' . DS . 'missing_model.ctp'); ?>
</p>