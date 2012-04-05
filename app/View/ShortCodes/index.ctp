<div class="shortcodes index">
	<h2><?php echo __('ShortCodes');?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('country');?></th>
			<th><?php echo $this->Paginator->sort('short code');?></th>
			<th><?php echo $this->Paginator->sort('international prefix');?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	foreach ($shortcodes as $shortcode): ?>
	<tr>
		<td><?php echo h($shortcode['ShortCode']['country']); ?>&nbsp;</td>
		<td><?php echo h($shortcode['ShortCode']['shortcode']); ?>&nbsp;</td>
		<td><?php echo h($shortcode['ShortCode']['international-prefix']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $shortcode['ShortCode']['_id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $shortcode['ShortCode']['_id']), null, __('Are you sure you want to delete # %s?', $shortcode['ShortCode']['_id'])); ?>
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
		<li><?php echo $this->Html->link(__('New ShortCode'), array('action' => 'add')); ?></li>
	</ul>
</div>
