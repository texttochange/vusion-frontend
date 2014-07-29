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
		    'jquery-ui/jquery-ui-1.10.3.custom.min',		    
		    'chosen/chosen-1.0.min',
		    'handsontable/jquery.handsontable-0.9.18.full',
		    'jstree/style.min'
		    ));		
		echo $this->Html->script('jquery-1.10.2.min.js');
		echo $this->Html->script('jqueryui/js/jquery-ui-1.10.3.custom.min.js');
		echo $this->Html->script('jqueryui/js/jquery-ui-timepicker-addon.js');		
		## general
		echo $this->Html->script('datejs/date.js');
		echo $this->Html->script('xregexp-2.0.0/xregexp-all.js');
		echo $this->Html->script('moment.js');
		echo $this->Html->script('chosen-1.0.jquery.min.js');
		## home brewed javascript		
		echo $this->Html->script('ttc-utils.js');		
		echo $this->Html->script('screen.js');
		echo $scripts_for_layout;
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
<body class="popup-layout">
	<div class="popup-container">
	    <div class="status-message">
		    <?php echo $this->element('status_message'); ?>
		</div>
		<div class="popup-header">
			<div class="ttc-left-header">
		        <?php 
		        echo $this->Html->image('vusion-logo-wide.png', array(
		        		'url' => array('controller'=> 'programs', 'action'=>'index')
		        		));
			    ?> 
			</div>			
		 </div>			
		<div id="content-popup" class="height-size">
        <?php         
		    echo $content_for_layout;		
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
