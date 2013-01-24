<div class="shortcodes index">
	<h3><?php echo __('ShortCodes');?></h3>
	<div class="ttc-display-area">
	<table cellpadding="0" cellspacing="0">
	<tr>
	<th><?php echo $this->Paginator->sort('shortcode', __('Shortcode'));?></th>
			<th><?php echo $this->Paginator->sort('country', __('Country'));?></th>
			<th><?php echo $this->Paginator->sort('international-prefix', __('International Prefix'));?></th>
			<th><?php echo $this->Paginator->sort('support-customized-id', __('Support Customized Id'));?></th>
			<th><?php echo $this->Paginator->sort('supported-internationally', __('Supported Internationally'));?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	foreach ($shortcodes as $shortcode): ?>
	<tr>		
		<td><?php echo $shortcode['ShortCode']['shortcode']; ?>&nbsp;</td>
		<td><?php echo $shortcode['ShortCode']['country']; ?>&nbsp;</td>
		<td><?php echo $shortcode['ShortCode']['international-prefix']; ?>&nbsp;</td>
		<td><?php echo ($shortcode['ShortCode']['support-customized-id']? __('yes'):__('no')); ?>&nbsp;</td>
    	<td><?php echo ($shortcode['ShortCode']['supported-internationally']? __('yes'):__('no')); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $shortcode['ShortCode']['_id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $shortcode['ShortCode']['_id']), null, __('Are you sure you want to delete the shortcode "%s"?', $shortcode['ShortCode']['shortcode'])); ?>
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
		<li><?php echo $this->Html->link(__('New ShortCode'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index')); ?></li>
	</ul>
</div>
