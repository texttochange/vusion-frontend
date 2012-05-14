<div class="templates index">
	<h3><?php echo __('Templates');?></h3>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('Name');?></th>
			<th><?php echo $this->Paginator->sort('Description');?></th>
			<th><?php echo $this->Paginator->sort('Updated');?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	foreach ($templates as $template): ?>
	<tr>
		<td><?php echo h($template['Template']['name']); ?>&nbsp;</td>
		<td><?php echo h($template['Template']['description']); ?>&nbsp;</td>
		<td><?php echo $this->Time->format('d/m/Y H:i:s',$template['Template']['modified']->sec); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $template['Template']['_id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $template['Template']['_id']), null, __('Are you sure you want to delete # %s?', $template['Template']['_id'])); ?>
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
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Template'), array('action' => 'add')); ?></li>
	</ul>
</div>
