<div class="programs form">
<h3><?php echo __('Add Program'); ?></h3>
<?php echo $this->Form->create('Program');?>
	<fieldset>
		
	<?php
		echo $this->Form->input('name');		
		echo $this->Form->input('url');
		echo $this->Form->input('database');
		echo $this->Html->tag('label',__('Import Dialogues From'));
		echo "<br/>";
		echo $this->Form->select('import-dialogues-from', $programOptions);
		echo "<br/><br/>";
		echo $this->Html->tag('label',__('Import Requests From'));
		echo "<br/>";
		echo $this->Form->select('import-requests-from', $programOptions);
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Programs'), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index')); ?></li>
	</ul>
</div>
