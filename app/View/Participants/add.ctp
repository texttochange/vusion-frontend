<div>
	<h2><?php echo __('Participants').' of '.$programName.' program';?></h2>
<div class="participants form">
	<?php echo $this->Form->create('Participant');?>
	<fieldset>
		<legend><?php echo __('Add Participant'); ?></legend>
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
		<li><?php echo $this->Html->link(__('Back To Program Home'),
	                array('program'=> $programUrl,'controller' => 'home'));
                ?></li>
                <li><?php echo $this->Html->link(__('Import Participant(s)'), array('program' => $programUrl, 'controller' => 'participants', 'action' => 'import')); ?></li>
		<li><?php echo $this->Html->link(__('View Participant(s)'), array('program' => $programUrl, 'controller' => 'participants', 'action' => 'index'));?></li>
	</ul>
</div>
</div>
