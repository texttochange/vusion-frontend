<div>
	<h2><?php echo __('Participants').' of '.$programName.' program';?></h2>
<div class="participants index">
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('phone');?></th>
			<th><?php echo $this->Paginator->sort('name');?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	foreach ($participants as $participant): ?>
	<tr>
		<td><?php echo h($participant['Participant']['phone']); ?>&nbsp;</td>
		<td><?php echo h($participant['Participant']['name']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('program' => $programUrl, 'controller' => 'participants', 'action' => 'view', $participant['Participant']['_id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('program' => $programUrl, 'controller' => 'participants', 'action' => 'edit', $participant['Participant']['_id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('program' => $programUrl, 'controller' => 'participants', 'action' => 'delete', $participant['Participant']['_id']), null, __('Are you sure you want to delete # %s?', $participant['Participant']['_id'])); ?>
		</td>
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
		echo $this->Paginator->prev('< ' . __('previous'), array('url'=> array('program' => $programUrl, 'controller' =>'participants')), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array('url'=> array('program' => $programUrl, 'controller' =>'participants')), null, array('class' => 'next disabled'));
	?>
	</div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Participant'), array('program' => $programUrl, 'controller' => 'participants', 'action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('Import Participant(s)'), array('program' => $programUrl, 'controller' => 'participants', 'action' => 'import')); ?></li>
	</ul>
</div>
</div>
