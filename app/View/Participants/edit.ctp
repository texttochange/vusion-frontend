<div>
	<h2><?php echo __('Participants').' of '.$programName.' program';?></h2>
<div class="participants form">
<?php echo $this->Form->create('Participant');?>
	<fieldset>
		<legend><?php echo __('Edit Participant'); ?></legend>
	<?php
		echo $this->Form->input('phone');
		echo $this->Form->input('name');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('program' => $programUrl, 'controller'=>'participants','action' => 'delete', $this->Form->value('Participant.id')), null, __('Are you sure you want to delete # %s?', $this->Form->value('Participant.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Participants'), array('program' => $programUrl, 'controller'=>'participants','action' => 'index'));?></li>
	</ul>
</div>
</div>
