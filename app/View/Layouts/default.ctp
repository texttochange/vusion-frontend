<?php
/**
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

$cakeDescription = __d('cake_dev', 'CakePHP: the rapid development php framework');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>
		<?php echo "Vusion" ?>:
		<?php echo $title_for_layout; ?>
		
	</title>
	<?php
	
		echo $this->Html->charset();
		
		echo $this->Html->meta('icon');

		echo $this->Html->css(array('cake.generic', 'basic', 'jquery-ui-1.8.16.custom'));

		echo $scripts_for_layout;
		
		echo $this->Html->script('jqueryui/js/jquery-1.6.2.min.js');
		echo $this->Html->script('jqueryui/js/jquery-ui-1.8.16.custom.min.js');
		echo $this->Html->script('dform/dform.js');
		echo $this->Html->script('dform/dform.extensions.js');
		echo $this->Html->script('dform/dform.subscribers.js');
		echo $this->Html->script('dform/dform.converters.js');
		echo $this->Html->script('form2js/form2js.js');
		echo $this->Html->script('ttc-generic-program.js');
	?>
</head>
<body>
	<div id="container">
		<div id="header">
			<?php 
				echo $this->Html->tag(
					'h1',
					'Vusion',
					array('class' => 'ttc-title')
					);
				?> 
				<div class="ttc-status"> 
				<?php
				if ($this->Session->read('Auth.User.id')) {	
					echo $this->Html->tag(
						'span', 
						'log as '.$this->Session->read('Auth.User.username').' ',
						array('class' => 'ttc-text')
						);
					echo $this->Html->link(
						'Logout',
						array('controller'=> 'users', 'action'=>'logout'), 
						array('class' => 'ttc-link'));
				}
				if (isset($programTimezone)) {
					echo $this->Html->tag('br');
					echo $this->Html->tag('span', 'program time: ');
					$now = new DateTime('now');
					date_timezone_set($now,timezone_open($programTimezone));
					echo $this->Html->tag('span', $now->format('H:i:s')  );
				}
				?> 
				</div> 
		</div> 
		<div id="content">

			<?php echo $this->Session->flash(); ?>

			<?php echo $content_for_layout; ?>

		</div>
		<div id="footer">
			<?php echo $this->Html->link(
					$this->Html->image('cake.power.gif', array('alt' => $cakeDescription, 'border' => '0')),
					'http://www.cakephp.org/',
					array('target' => '_blank', 'escape' => false)
				);
			?>
		</div>
	</div>
	<?php echo $this->element('sql_dump'); ?>
</body>
</html>