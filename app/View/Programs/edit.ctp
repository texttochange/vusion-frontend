<div class="programs form">
<?php echo $this->Form->create('Program');?>
	<fieldset>
		<legend><?php echo __('Edit Program'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('name');
		echo $this->Form->input('country');
		?>
		<div class='input text'>
		<?php
		echo $this->Html->tag('label',__('Timezone'));
		$timezone_identifiers = DateTimeZone::listIdentifiers();
		$timezone_options = array();
		foreach($timezone_identifiers as $timezone_identifier) {
			$timezone_options[$timezone_identifier] = $timezone_identifier; 
		}
		echo $this->Form->select('timezone', $timezone_options);
		//echo $this->Form->select('timezone', $timezone_identifiers, array('value'=>'412'));
		?>
		</div>
		<?php
		echo $this->Form->input('url');
		echo $this->Form->input('database');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Program.id')), null, __('Are you sure you want to delete # %s?', $this->Form->value('Program.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Programs'), array('action' => 'index'));?></li>
	</ul>
</div>
