<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>
    <?php if (isset($programName)) echo $programName." - "; ?>	
    <?php echo "Vusion" ?>
	</title>
	<?php
	
		echo $this->Html->charset();
		
		echo $this->Html->meta('icon');

		echo $this->Html->css(array(
		    'cake.generic',
		    'basic',
		    'jquery-ui-1.8.16.custom',
		    'superfish',
		    'superfish-vertical'
		    ));

		echo $scripts_for_layout;
		
		echo $this->Html->script('jqueryui/js/jquery-1.6.2.min.js');
		echo $this->Html->script('jqueryui/js/jquery-ui-1.8.16.custom.min.js');
		echo $this->Html->script('jqueryui/js/jquery.ui.datepicker.js');
		echo $this->Html->script('jqueryui/js/jquery-ui-timepicker-addon.js');
		echo $this->Html->script('dform/dform.js');
		echo $this->Html->script('dform/dform.extensions.js');
		echo $this->Html->script('dform/dform.subscribers.js');
		echo $this->Html->script('dform/dform.converters.js');
		echo $this->Html->script('form2js/form2js.js');
		echo $this->Html->script('ttc-generic-program.js');
		echo $this->Html->script('ttc-utils.js');
		echo $this->Html->script('datejs/date.js');
		echo $this->Html->script('jquery.validate.js');
		echo $this->Html->script('superfish-1.4.8/superfish.js');
		echo $this->Html->script('superfish-1.4.8/hoverIntent.js');
		echo $this->Html->script('superfish-1.4.8/supersubs.js');
		          
                ?><script><?php 
                echo $this->element('localization');
		?></script><?php 

		if (isset($this->Js)) {
		//disappear success flash messages
		$this->Js->get('document')->event('ready', '
			$("[class*=success]").delay(5000).fadeOut(1000);
			');
		}
	?>
</head>
<body>
	<div id="container">
		<div id="header">
			<div class="ttc-left-header">
		        <?php 
		        echo $this->Html->image('vusion-logo-wide.png', array(
		            'url' => array('controller'=> 'programs')
		        ));
			?> 
			</div>
			
			<div class="ttc-right-header"> 
			<?php
			if ($this->Session->read('Auth.User.id')) {	
				echo $this->Html->tag(
					'span', 
					'log as '.$this->Session->read('Auth.User.username').' '
					);
			}
			?> 
			</div>
<div class="ttc-central-header">
			<?php
			if ($this->Session->read('Auth.User.id')) {	
			    echo $this->Html->link(
					__('logout'),
					array('controller'=> 'users', 'action'=>'logout'), 
					array('class' => 'ttc-link-header'));
			}
			if (isset($isAdmin) && $isAdmin) {
			    echo $this->Html->link(
			        __('admin'),
			        array('controller'=>'admin'),
			        array('class'=>'ttc-link-header'));
				//print_r($vumiStatus);
			}
			?>
			</div> 
		</div>     
			<?php 
			     echo $this->Session->flash(); 
			     if (!$this->Session->flash()) {
			         echo $this->Html->tag('div', '', array(
			             'id' => 'flashMessage', 
			             'class' => 'message', 
			             'style' => 'display: none')
			             );
			     } 
			     
			     ?>
			
			<!-- To be refact with all the Controllers and views -->
			<?php if (isset($programName)) { ?>
				<div class='ttc-program-header'>
				<div class="ttc-program-time">
				<?php
				    if ($programTimezone) {
				        echo $this->Html->tag('span', $programTimezone.' - ');
				        $now = new DateTime('now');
				        date_timezone_set($now,timezone_open($programTimezone));
				        echo $this->Html->tag('span', $now->format('H:i:s')  );
				        $this->Js->get('document')->event('ready','setInterval("updateClock()", 1000);');
				    }
				?>
				</div>
				<div class='ttc-program-title'>
				<?php
				    echo $this->Html->link($programName, 
					array('program' => $programUrl,
					      'controller' => 'programHome',
					      'action' => 'index'
					      ), array('style'=>'text-decoration:none;font-weight:normal; font-size:22px'));
				
				    //echo " > ";
				?>
				</div>
				
				<div class="ttc-program-link">
				<?php
				    echo "> ";
				    echo $this->Html->link($this->params['controller'], 
					array('program' => $programUrl,
					      'controller' => $this->params['controller'],
					      'action' => 'index'
					      ),
				        array('style'=>'text-decoration:none;font-weight:normal; font-size:12px'));
				    if(isset($this->params['action']) &&  $this->params['action'] != 'index') {
				        echo " > ";
					echo $this->Html->link($this->params['action'], 
					    array('program' => $programUrl,
						  'controller' => $this->params['controller'],
						  'action' => $this->params['action']
						  ),
				        array('style'=>'text-decoration:none;font-weight:normal; font-size:12px'));
				    }				    
				?>
				
				</div>
				</div>				
			<?php } ?>
			
		<div id="content">
			
			<?php echo $content_for_layout; ?>

		</div>
		<?php 
		    if (isset($programName)) {
		        echo $this->element('navigation_menu');
		        echo $this->element('backend_notifications');
		    }
		?>
		<div id="footer">
		</div>
	</div>
	<?php
	echo $this->Html->tag('div', 'See Database Dump', array('id'=>'show-database-dump', 'style'=>'color:black;background:white'));
	$this->Js->get('#show-database-dump')->event(
	    'click',
	    '$("#sql_dump").show()');
	?>
	<div id='sql_dump' style='display:none'>
	<?php
	echo $this->element('sql_dump'); 
	?>
	</div>
	<?php 
	    if (isset($this->Js)) {
	        echo $this->Js->writeBuffer();
	    }
	?>
</body>
</html>