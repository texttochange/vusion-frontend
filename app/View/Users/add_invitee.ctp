<div class="users form users-index program-body">
<h3><?php echo __('Add User'); ?></h3>
<?php echo $this->Form->create('User', array('action' => 'addInvitee'));?>
	<fieldset>
		
	<?php
		echo $this->Form->input('username', array('label' => __('Username')));
		echo $this->Form->input('password', array('label' => __('Password')));
		echo $this->Form->input('email', array('label' => __('Email')));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="admin-action">
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Users'), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index')); ?></li>
	</ul>
</div>
</div>
