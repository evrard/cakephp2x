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
 * @subpackage    cake.cake.libs.view.templates.elements
 * @since         CakePHP(tm) v 0.10.5.1782
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>
<div id="cakeControllerDump">
	<h2><?php __('Controller dump:', false); ?></h2>
	<pre>
		<?php echo h(print_r($controller, true)); ?>
	</pre>
</div>