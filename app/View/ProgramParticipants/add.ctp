<div>
		
<div class="participants form">
    <ul class="ttc-actions">
		<li><?php echo $this->Html->link(__('Import Participant(s)'), array('program' => $programUrl, 'controller' => 'programParticipants', 'action' => 'import')); ?></li>
		<li><?php echo $this->Html->link(__('View Participant(s)'), array('program' => $programUrl, 'controller' => 'programParticipants'));?></li>
	</ul>
	<br /><br /><br />
	<h3><?php echo __('Add Participant'); ?></h3>
	<?php echo $this->Form->create('Participant');?>
	<fieldset>
		
	<?php
		echo $this->Form->input('phone');
		echo $this->Form->input('name');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>

