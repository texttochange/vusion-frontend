<div class="groups form users-index program-body">
<h3><?php echo __('Add Group'); ?></h3>
<?php echo $this->Form->create('Group');?>
	<fieldset>
		
	<?php
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
		<li><?php echo $this->Html->link(__('List Groups'), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index')); ?></li>
	</ul>
</div>
</div>
