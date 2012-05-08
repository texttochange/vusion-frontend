<div class="unattached_messages form">
<h3><?php echo __('Add Unattached Message'); ?></h3>
<ul class="ttc-actions">
    <li></li>
</ul>
<?php echo $this->Form->create('UnattachedMessage');?>

	<fieldset>
	
	<?php
        $otions = array();
        $options['all participants'] = "all participants";
        echo "<div class='input-text'>";
        echo $this->Html->tag('label',__('Send To'));
		echo $this->Form->select('to', $options);
		echo "</div>";
		echo $this->Form->input(__('content'), array('rows'=>5));
		echo $this->Form->input(__('schedule'), array('id'=>'schedule'));
		$this->Js->get('document')->event('ready','$("#schedule").datetimepicker();
		                                           addContentFormHelp();');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>

<?php echo $this->Js->writeBuffer(); ?>
