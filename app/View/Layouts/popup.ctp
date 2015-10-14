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
		    ));	
		$this->RequireJs->scripts(array('jquery'));
		echo $scripts_for_layout;
    ?><script>
    <?php echo $this->element('localization');?>
    </script>
    <?php
		echo $this->Html->meta(array('name'=>'robots', 'content'=> 'noindex'));
	?>	
</head>
<body class="popup-layout">
	<div class="popup-container">
		<?php echo $this->element('flash_message', array('asTable' => false)); ?>
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
	    if (isset($this->RequireJs)) {
	    		echo $this->RequireJs->writeBuffer();
	    }
	    ?>
</body>
</html>
