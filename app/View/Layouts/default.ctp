<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>
    <?php if (isset($programDetails['name'])) echo $programDetails['name']." - "; ?>	
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
		    'superfish-vertical',
		    'chosen'
		    ));
		echo $scripts_for_layout;		
		echo $this->Html->script('jquery-1.7.2.min.js');
		echo $this->Html->script('jqueryui/js/jquery-ui-1.8.16.custom.min.js');
		echo $this->Html->script('jqueryui/js/jquery.ui.datepicker.js');
		echo $this->Html->script('jqueryui/js/jquery-ui-timepicker-addon.js');
		echo $this->Html->script('jquery.validate-1.9.0.js');
		echo $this->Html->script('dform/dform.js');
		echo $this->Html->script('dform/dform.extensions.js');
		echo $this->Html->script('dform/dform.subscribers.js');
		echo $this->Html->script('dform/dform.converters.js');
		echo $this->Html->script('form2js/form2js.js');
		echo $this->Html->script('ttc-generic-program.js');
		echo $this->Html->script('ttc-utils.js');
		echo $this->Html->script('superfish-1.4.8/superfish.js');
		echo $this->Html->script('superfish-1.4.8/hoverIntent.js');
		echo $this->Html->script('superfish-1.4.8/supersubs.js');
		echo $this->Html->script('datejs/date.js');
		echo $this->Html->script('moment.js');
		echo $this->Html->script('chosen.jquery.min.js');
		echo $this->Html->script('counter.js');
		echo $this->Html->script('screen.js');
    ?><script>
    <?php echo $this->element('localization');?>
    </script>
    <?php
    if (isset($this->Js)) {
    		//disappear success flash messages
    		$this->Js->get('document')->event('ready', '
    				$("[class*=success]").delay(5000).fadeOut(1000);
    				');
    }
	?>
	<?php
	echo $this->Html->meta(array('name'=>'robots', 'content'=> 'noindex'));
	?>	
</head>
<body>
	<div id="container">
		<div id="header">
			<div class="ttc-left-header">
		        <?php 
		        echo $this->Html->image('vusion-logo-wide.png', array(
		        		'url' => array('controller'=> 'programs', 'action'=>'index')
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
							__('Logout'),
							array('controller'=> 'users', 'action'=>'logout'), 
							array('class' => 'ttc-link-header'));
					echo $this->Html->link(
							__('My Account'),
							array('controller'=> 'users', 'action'=>'view', $this->Session->read('Auth.User.id')), 
							array('class' => 'ttc-link-header'));
			}
			if (isset($isAdmin) && $isAdmin) {
					echo $this->Html->link(
							__('Admin'),
							array('controller'=>'admin'),
							array('class'=>'ttc-link-header'));				
			}
			?>
			</div> 
		 </div>    
		 <div class="status-message">
		    <table class="status-table" cellpadding="0" cellspacing="0" align="center">
		    <tr>
		    <td>
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
			</td></tr>
			<tr>
			<td>
			<?php
			echo $this->Html->tag('div', '', array(
					'id' => 'connectionState',
					'class' => 'connection-message',
					'style' => 'display: none')
					);			
			 ?>
			 </td></tr>
			 </table>
			</div>
			<!-- To be refact with all the Controllers and views -->
			<?php 
			if (isset($programDetails['name'])) { 
					 echo $this->element('program_specific_header');					
			} 
			?>
		<div id="content">
        <?php
        if (isset($programDetails['name'])) {
            echo "<div class='program-left-column'>";			   
            echo $this->element('navigation_menu');
            echo $this->element('backend_notifications');
            echo "</div>";
            echo "<div class='program-body'>";
			echo $content_for_layout;
			echo "</div>";   
		} else {		    
		    echo $content_for_layout;
		}
		?>   
		</div>		   
     </div>
	<div id="footer">
		    <?php echo $this->element('footer'); ?>
    </div>
		<?php 
	    if (isset($this->Js)) {
	    		echo $this->Js->writeBuffer();
	    }
	    ?>
</body>
</html>