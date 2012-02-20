<div>
	<h2><?php echo __('Program History').' of '.$programName.' program';?></h2>
<div class="status index">
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('phone');?></th>
			<th><?php echo $this->Paginator->sort('message');?></th>
			<th><?php echo $this->Paginator->sort('time');?></th>
	</tr>
	<?php
	foreach ($statuses as $status): ?>
	<tr>
		<td><?php echo h($status['ParticipantsState']['participant-phone']); ?>&nbsp;</td>
		<?php if (isset($status['ParticipantsState']['message']['content'])) { ?>
		<td><?php echo h($status['ParticipantsState']['message']['content']); ?>&nbsp;</td>
		<?php } else { ?>
		<td><?php echo h($status['ParticipantsState']['type']); ?>&nbsp;</td>
		<?php } ?>
		<td><?php echo h($status['ParticipantsState']['datetime']); ?>&nbsp;</td>
	</tr>
	<?php endforeach; ?>
	</table>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</p>

	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array('url'=> array('program' => $programUrl, 'controller' =>'status')), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array('url'=> array('program' => $programUrl, 'controller' =>'status')), null, array('class' => 'next disabled'));
	?>
</div>
	
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Back Homepage'), array('program'=>$programUrl,'controller'=>'home')); ?></li>
	</ul>
</div>	
