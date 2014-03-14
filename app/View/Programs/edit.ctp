<div class="programs form program-edit">
<h3><?php echo __('Edit Program'); ?></h3>
<?php echo $this->Form->create('Program');?>
	<fieldset>
		
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('name', array('label' => 'Name'));		
		echo $this->Form->input('url');
		echo $this->Form->input('database',
		    array('label' => 'Database',
		        'readonly' => 'true',
		        'style' => 'color:#AAAAAA'));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="admin-action">
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
	<li><?php echo $this->Form->postLink(__('Delete Program'), array('action' => 'delete', $this->Form->value('Program.id')), null, __('Are you sure you want to delete program "%s" ?', $this->Form->value('Program.name'))); ?></li>
		<li><?php echo $this->Html->link(__('List Programs'), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index')); ?></li>
	</ul>
</div>
</div>
