<div class="groups form users-index program-body">
<h3><?php echo __('Edit Group'); ?></h3>
<?php echo $this->Form->create('Group',  array('type' => 'post'));?>
	<fieldset>
	
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('name', array('label' => __('Name')));
		echo $this->Form->input('specific_program_access', array('label' => __('Specific Program Access')));
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
