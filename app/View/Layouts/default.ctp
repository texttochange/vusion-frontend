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
		    'admin',
		    'jquery-ui/jquery-ui-1.10.3.custom.min',
		    'superfish/superfish-1.7.4',
		    'superfish/superfish-vertical-1.7.4',
		    //'superfish/superfish-navbar-1.7.4',		    
		    'superfish/megafish-1.7.4',
		    'chosen/chosen-1.0.min',
		    'handsontable/jquery.handsontable-0.9.18.full',
		    'jstree/style.min'
		    ));	
		//echo $this->Html->script('require.js', array('data-main' => '/js/main.js'));	
		echo $this->Html->script('jquery-1.10.2.min.js');
		echo $this->Html->script('jqueryui/js/jquery-ui-1.10.3.custom.min.js');
		echo $this->Html->script('jqueryui/js/jquery-ui-timepicker-addon.js');
		## dynamic form
		echo $this->Html->script('jquery.validate-1.9.0.js');
		echo $this->Html->script('dform/dform.js');
		echo $this->Html->script('dform/dform.extensions.js');
		echo $this->Html->script('dform/dform.subscribers.js');
		echo $this->Html->script('dform/dform.converters.js');
		echo $this->Html->script('form2js/form2js.js');
		echo $this->Html->script('form2js/js2form.utils.js');
		## nav menu
		echo $this->Html->script('superfish-1.7.4/superfish.min.js');
		echo $this->Html->script('superfish-1.7.4/hoverIntent.js');
		echo $this->Html->script('superfish-1.7.4/supersubs.js');
		## general
		echo $this->Html->script('datejs/date.js');
		echo $this->Html->script('xregexp-2.0.0/xregexp-all.js');
		echo $this->Html->script('moment.js');
		echo $this->Html->script('chosen-1.0.jquery.min.js');
		## home brewed javascript
		echo $this->Html->script('ttc-dynamic-form-structure.js');
		echo $this->Html->script('ttc-generic-program.js');
		echo $this->Html->script('ttc-utils.js');
		echo $this->Html->script('counter.js');
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
    <?php  echo $this->Html->meta(array('name'=>'robots', 'content'=> 'noindex')); ?>	
</head>
<body>
<div id="container">
	<div class="status-message">
		<?php echo $this->element('status_message'); ?>
	</div>
	<!-- To be refact with all the Controllers and views -->
	<div id="header">
	<?php echo $this->element('header_vusion'); ?>
	</div>
	<?php if (isset($programDetails['name'])) : ?>
	<div class='ttc-program-header <?php if ($programDetails['status']==='archived') { echo "archived";} ?>'>
		<?php echo $this->element('header_program_specific');?>
	</div> 
	<?php endif; ?>
	<div id="content" class="height-size">
           <?php
                if (isset($programDetails['name'])) {
                    $this->Js->set('isProgramSpecific', true);?>
                    <div class="table">
                        <div class="heading">
                            <div class="row" style='border-spacing:0px'>
                              <div class="cell">
                                  <div class='program-left-column'>
                                  <?php
                                        echo $this->element('navigation_menu');
                                        if ($programDetails['status'] == 'running') {
                                            if (isset($programDetails['settings']['shortcode']) 
                                                && isset($programDetails['settings']['timezone'])) {
                                            echo $this->element('program_statistics');
                                                }
                                                echo $this->element('backend_notifications');
                                        } else {  //archived program
                                            echo $this->element('program_statistics');
                                        } 
                                    ?>
                                    </div>
                                </div>
                                <div class="cell">
                                    <div class='program-body '>
                                    <?php echo $content_for_layout; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
           <?php } else {		    
                    echo $content_for_layout;
                } ?>
	</div>
	<div id="footer">
		<?php echo $this->element('footer'); ?>
	</div>
</div>

<?php 
if (isset($this->Js)) {
	echo $this->Js->writeBuffer();
}
?>
</body>
</html>