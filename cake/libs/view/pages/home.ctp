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
 * @copyright     Copyright 2005-2009, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.cake.libs.view.templates.pages
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */
if (Configure::read() == 0):
	$this->cakeError('error404');
endif;
?>
<h2><?php echo sprintf(__('Release Notes for CakePHP %s.'), Configure::version()); ?></h2>
<?php
echo $this->Html->link(__('Read the changelog'), 'http://code.cakephp.org/wiki/changelog/1_3_0-alpha');

if (Configure::read() > 0):
	Debugger::checkSessionKey();
endif;
?>
<p>
	<?php
		if (is_writable(TMP)):
			echo '<span class="notice success">';
				__('Your tmp directory is writable.', false);
			echo '</span>';
		else:
			echo '<span class="notice">';
				__('Your tmp directory is NOT writable.', false);
			echo '</span>';
		endif;
	?>
</p>
<p>
	<?php
		$settings = Cache::settings();
		if (!empty($settings)):
			echo '<span class="notice success">';
				echo sprintf(
					__('The %s is being used for caching. To change the config edit APP/config/core.php '),
					'<em>'. $settings['engine'] . 'Engine</em>');
			echo '</span>';
		else:
			echo '<span class="notice">';
				__('Your cache is NOT working. Please check the settings in APP/config/core.php', false);
			echo '</span>';
		endif;
	?>
</p>
<p>
	<?php
		$filePresent = null;
		if (file_exists(CONFIGS.'database.php')):
			echo '<span class="notice success">';
				__('Your database configuration file is present.', false);
				$filePresent = true;
			echo '</span>';
		else:
			echo '<span class="notice">';
				__('Your database configuration file is NOT present.', false);
				echo '<br/>';
				__('Rename config/database.php.default to config/database.php', false);
			echo '</span>';
		endif;
	?>
</p>
<?php
if (isset($filePresent)):
	if (!class_exists('ConnectionManager')) {
		require LIBS . 'model' . DS . 'connection_manager.php';
	}
	$db = ConnectionManager::getInstance();
	@$connected = $db->getDataSource('default');
?>
<p>
	<?php
		if ($connected->isConnected()):
			echo '<span class="notice success">';
	 			__('Cake is able to connect to the database.', false);
			echo '</span>';
		else:
			echo '<span class="notice">';
				__('Cake is NOT able to connect to the database.', false);
			echo '</span>';
		endif;
	?>
</p>
<?php endif;?>
<h3><?php __('Editing this Page', false); ?></h3>
<p>
<?php
__('To change the content of this page, create: APP/views/pages/home.ctp.<br />
To change its layout, create: APP/views/layouts/default.ctp.<br />
You can also add some CSS styles for your pages at: APP/webroot/css.', false);
?>
</p>

<h3><?php __('Getting Started', false); ?></h3>
<p>
	<?php
		echo $this->Html->link(
			sprintf('<strong>%s</strong> %s', __('new'), __('CakePHP 1.2 Docs')),
			'http://book.cakephp.org',
			array('target' => '_blank', 'escape' => false)
		);
	?>
</p>
<p>
	<?php
		echo $this->Html->link(
			__('The 15 min Blog Tutorial', false),
			'http://book.cakephp.org/view/219/the-cakephp-blog-tutorial',
			array('target' => '_blank', 'escape' => false)
		);
	?>
</p>

<h3><?php __('More about Cake'); ?></h3>
<p>
<?php __('CakePHP is a rapid development framework for PHP which uses commonly known design patterns like Active Record, Association Data Mapping, Front Controller and MVC.', false); ?>
</p>
<p>
<?php __('Our primary goal is to provide a structured framework that enables PHP users at all levels to rapidly develop robust web applications, without any loss to flexibility.', false); ?>
</p>

<ul>
	<li><a href="http://www.cakefoundation.org/"><?php __('Cake Software Foundation', false); ?> </a>
	<ul><li><?php __('Promoting development related to CakePHP', false); ?></li></ul></li>
	<li><a href="http://www.cakephp.org"><?php __('CakePHP', false); ?> </a>
	<ul><li><?php __('The Rapid Development Framework', false); ?></li></ul></li>
	<li><a href="http://book.cakephp.org"><?php __('CakePHP Documentation', false); ?> </a>
	<ul><li><?php __('Your Rapid Development Cookbook', false); ?></li></ul></li>
	<li><a href="http://api.cakephp.org"><?php __('CakePHP API', false); ?> </a>
	<ul><li><?php __('Quick Reference', false); ?></li></ul></li>
	<li><a href="http://bakery.cakephp.org"><?php __('The Bakery', false); ?> </a>
	<ul><li><?php __('Everything CakePHP', false); ?></li></ul></li>
	<li><a href="http://live.cakephp.org"><?php __('The Show', false); ?> </a>
	<ul><li><?php __('The Show is a live and archived internet radio broadcast CakePHP-related topics and answer questions live via IRC, Skype, and telephone.', false); ?></li></ul></li>
	<li><a href="http://groups.google.com/group/cake-php"><?php __('CakePHP Google Group', false); ?> </a>
	<ul><li><?php __('Community mailing list', false); ?></li></ul></li>
	<li><a href="irc://irc.freenode.net/cakephp">irc.freenode.net #cakephp</a>
	<ul><li><?php __('Live chat about CakePHP', false); ?></li></ul></li>
	<li><a href="http://code.cakephp.org/"><?php __('CakePHP Code', false); ?> </a>
	<ul><li><?php __('For the Development of CakePHP (Tickets, Git browser, Roadmap, Changelogs)', false); ?></li></ul></li>
	<li><a href="http://www.cakeforge.org"><?php __('CakeForge', false); ?> </a>
	<ul><li><?php __('Open Development for CakePHP', false); ?></li></ul></li>
	<li><a href="http://astore.amazon.com/cakesoftwaref-20/"><?php __('Book Store', false); ?> </a>
	<ul><li><?php __('Recommended Software Books', false); ?></li></ul></li>
	<li><a href="http://www.cafepress.com/cakefoundation"><?php __('CakePHP gear', false); ?> </a>
	<ul><li><?php __('Get your own CakePHP gear - Doughnate to Cake', false); ?></li></ul></li>
</ul>