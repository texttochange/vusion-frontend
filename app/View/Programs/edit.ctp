<div class="programs form">
<h3><?php echo __('Edit Program'); ?></h3>
<?php echo $this->Form->create('Program');?>
	<fieldset>
		
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('name');		
		echo $this->Form->input('url');
		echo $this->Form->input('database');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Form->postLink(__('Delete Program'), array('action' => 'delete', $this->Form->value('Program.id')), null, __('Are you sure you want to delete # %s?', $this->Form->value('Program.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Programs'), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index')); ?></li>
	</ul>
</div>
