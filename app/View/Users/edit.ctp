<div class="users form">
<h3><?php echo __('Edit User'); ?></h3>
<?php echo $this->Form->create('User');?>
	<fieldset>
		
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('username');
		echo $this->Html->tag('label',__('Password'));
		echo $this->Html->link(__('Change Password'), array('action' => 'changePassword', $this->Form->value('User.id')));
		echo $this->Form->input('email');
		echo $this->Form->input('group_id');
		echo $this->Form->input('Program');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete User'), array('action' => 'delete', $this->Form->value('User.id')), null, __('Are you sure you want to delete the user "%s" ?', $this->Form->value('User.username'))); ?></li>
		<li><?php echo $this->Html->link(__('List Users'), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index')); ?></li>
	</ul>
</div>
