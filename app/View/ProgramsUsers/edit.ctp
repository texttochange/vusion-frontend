<div class="programsUsers form">
<?php echo $this->Form->create('ProgramsUser');?>
	<fieldset>
		<legend><?php echo __('Edit Programs User'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('program_id', array('type' => 'select', 'options'=> $programs));
		echo $this->Form->input('user_id', array('type' => 'select', 'options'=> $users));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('ProgramsUser.id')), null, __('Are you sure you want to delete # %s?', $this->Form->value('ProgramsUser.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Programs Users'), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('List Programs'), array('controller' => 'programs', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Program'), array('controller' => 'programs', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
