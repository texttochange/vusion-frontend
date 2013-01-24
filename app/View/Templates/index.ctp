<div class="templates index">
	<h3><?php echo __('Templates');?></h3>
	<div class="ttc-display-area">
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('name');?></th>
			<th><?php echo $this->Paginator->sort('type-template', 'Type');?></th>
			<th><?php echo $this->Paginator->sort('template');?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	foreach ($templates as $template): ?>
	<tr>
		<td><?php echo h($template['Template']['name']); ?>&nbsp;</td>
		<td><?php echo __($template['Template']['type-template']) ?></td>
		<td><?php echo __($template['Template']['template']) ?></td>
		<td class="actions">
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $template['Template']['_id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $template['Template']['_id']), null, __('Are you sure you want to delete "%s"?', $template['Template']['name'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	</div>
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
		<li><?php echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index')); ?></li>
	</ul>
</div>
