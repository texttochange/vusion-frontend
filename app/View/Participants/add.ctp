<div>
		
<div class="participants form">
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
