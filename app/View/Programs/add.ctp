<div class="admin-action">
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Programs'), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index')); ?></li>
	</ul>
</div>
</div>
<div class="programs form program-edit">
<div class="table">
<div class="row">
<div class="cell">
<h3><?php echo __('Add Program'); ?></h3>
<?php echo $this->Form->create('Program');?>
	<fieldset>
		
	<?php
		echo $this->Form->input('name', array('label' => __('Name')));		
		echo $this->Form->input('url');
		echo $this->Form->input('database', array('label' => __('Database')));
		echo "<div>";
		echo $this->Html->tag('label',__('Import Dialogues and Request From'));
		echo "<br/>";
		echo $this->Form->select('import-dialogues-requests-from', $programOptions);
		echo "</div>";
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
</div>
</div>
</div>
