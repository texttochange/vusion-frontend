<div class="unattached_messages form">
<h3><?php echo __('Edit Separate Message'); ?></h3>
<?php echo $this->Form->create('UnattachedMessage');?>

	<fieldset>
	
	<?php
        $options = array();
        $options['all participants'] = "all participants";
        echo "<div class='input-text'>";
        echo $this->Html->tag('label',__('Send To'));
		echo $this->Form->select('to', $options);
		echo "</div>";
		echo $this->Form->input(__('content'), array('rows'=>5));
		echo $this->Form->input(__('schedule'), array('id'=>'schedule',
		                                              'value'=>$this->Time->format('d/m/Y H:i', $this->data['UnattachedMessage']['schedule'])));
		$this->Js->get('document')->event('ready','$("#schedule").datetimepicker();
		                                           addContentFormHelp("http://'.env("HTTP_HOST").'");');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>

<?php echo $this->Js->writeBuffer(); ?>
