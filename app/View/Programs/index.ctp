<div class="programs index">
	<h3><?php echo __('Programs');?></h3>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('name');?></th>
			<th><?php echo $this->Paginator->sort('country');?></th>
			<th><?php echo $this->Paginator->sort('url');?></th>
			<th><?php echo $this->Paginator->sort('database');?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	foreach ($programs as $program): ?>
	<tr>
		<td><?php echo h($program['Program']['name']); ?>&nbsp;</td>
		<td><?php echo h($program['Program']['country']); ?>&nbsp;</td>
		<td><?php echo h($program['Program']['url']); ?>&nbsp;</td>
		<td><?php echo h($program['Program']['database']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('Home'), '/'.$program['Program']['url']);?>
			<?php if ($isProgramEdit) { ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $program['Program']['id'])); ?>
			<?php echo $this->Form->postLink(__('Archive'), array('action' => 'delete', $program['Program']['id']), null, __('Are you sure you want to archive # %s?', $program['Program']['name'])); ?>
			<?php }; ?>
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
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
</div>
<?php if ($isProgramEdit) { ?>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Program'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('Unmatchable Replies'), array('controller'=>'unmatchableReply','action' => 'index')); ?></li>
	</ul>
</div>
<?php }; ?>
