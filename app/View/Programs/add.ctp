<div class="programs form">
<?php echo $this->Form->create('Program');?>
	<fieldset>
		<legend><?php echo __('Add Program'); ?></legend>
	<?php
		echo $this->Form->input('name');
		echo $this->Form->input('country');
		echo $this->Form->input('url');
		echo $this->Form->input('database');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Programs'), array('action' => 'index'));?></li>
	</ul>
</div>
