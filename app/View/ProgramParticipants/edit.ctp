<div>
	
<div class="participants form">
    <ul class="ttc-actions">
		<li><?php echo $this->Form->postLink(__('Delete'), array('program' => $programUrl, 'controller'=>'programParticipants','action' => 'delete', $this->Form->value('Participant.id')), null, __('Are you sure you want to delete # %s?', $this->Form->value('Participant.id'))); ?></li>
	</ul>
<h3><?php echo __('Edit Participant'); ?></h3>
<?php echo $this->Form->create('Participant');?>
	<fieldset>
	<?php
		echo $this->Form->input('phone');
		echo $this->Form->input('name');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>

</div>
