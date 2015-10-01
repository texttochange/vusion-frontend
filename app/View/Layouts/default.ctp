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
                    <div class="table" style="width:100%">
                        <div class="heading">
                            <div class="row" style='border-spacing:0px'>
                              <div class="cell" style="width:200px">
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
                                <div class="cell" style="width:100%">
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
	echo $this->RequireJs->writeBuffer();
}
?>
</body>
</html>