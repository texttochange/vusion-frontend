<div>
	
<div class="status index">
	<h3><?php echo __('Program History'); ?></h3>
	<table cellpadding="0" cellspacing="0">
	<tr>
                        <th class="ttc-filter">
                        <?php
                           echo $this->Form->create(null); 
                        ?>
                        <?php
                           $options = array(); 
                           $options['non_matching_answers'] = "non_matching_answers";
                        ?>
	                <?php
	                   echo $this->Form->select('filter', $options, array('id'=> 'filter', 'empty' => '...Filter results...'));
	                   $this->Js->get('#filter')->event('change', '
	                     window.location.search = "?"+$("select option:selected").text();
	                   ');
	                ?>
	                <?php echo $this->Form->end(); ?>
	                </th>
	</tr>
	<tr>                                                                        
			<th><?php echo $this->Paginator->sort('phone', null, array('url'=> array('program' => $programUrl)));?></th>
			<th><?php echo $this->Paginator->sort('type', null, array('url'=> array('program' => $programUrl)));?></th>
			<th><?php echo $this->Paginator->sort('status', null, array('url'=> array('program' => $programUrl)));?></th>
			<th><?php echo $this->Paginator->sort('message', null, array('url'=> array('program' => $programUrl)));?></th>
			<th><?php echo $this->Paginator->sort('time', null, array('url'=> array('program' => $programUrl)));?></th>
	</tr>
	<?php
	foreach ($statuses as $status): ?>
	<tr>
		<td><?php echo h($status['History']['participant-phone']); ?>&nbsp;</td>
		<td><?php echo h($status['History']['message-type']); ?>&nbsp;</td>
		<td><?php echo h($status['History']['message-status']); ?>&nbsp;</td>
		<td><?php echo h($status['History']['message-content']); ?>&nbsp;</td>
		<td><?php echo h($status['History']['timestamp']); ?>&nbsp;</td>
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
		echo $this->Paginator->prev('< ' . __('previous'), array('url'=> array('program' => $programUrl)), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => '', 'url'=> array('program' => $programUrl)));
		echo $this->Paginator->next(__('next') . ' >', array('url'=> array('program' => $programUrl)), null, array('class' => 'next disabled'));
	?>
</div>
	
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Back Homepage'), array('program'=>$programUrl,'controller'=>'programHome')); ?></li>
		<li><?php echo $this->Html->link('Export Program History', array('program' => $programUrl, 'controller' => 'programHistory', 'action' => 'export.csv')); ?></li>
	</ul>
</div>	
<?php echo $this->Js->writeBuffer(); ?>
