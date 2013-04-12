<div class="groups form">
<h3><?php echo __('Edit Group'); ?></h3>
<?php echo $this->Form->create('Group');?>
	<fieldset>
	
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('name');
		echo $this->Form->input('specific_program_access');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="admin-action">
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Form->postLink(__('Delete Group'), array('action' => 'delete', $this->Form->value('Group.id')), null, __('Are you sure you want to delete "%s" group?', $this->Form->value('Group.name'))); ?></li>
		<li><?php echo $this->Html->link(__('List Groups'), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index')); ?></li>
	</ul>
</div>
</div>
