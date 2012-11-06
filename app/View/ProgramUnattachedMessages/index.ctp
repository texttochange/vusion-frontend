<div class="unattached_messages index">
    <ul class="ttc-actions">
		<li><?php echo $this->Html->link(__('New Separate Message'), array('program'=>$programUrl, 'action' => 'add'), array('class' => 'ttc-button')); ?></li>
	</ul>
	<h3><?php echo __('Separate Messages');?></h3>
	<div class="ttc-display-area"> 
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('name', null, array('url'=> array('program' => $programUrl)));?></th>
			<th><?php echo $this->Paginator->sort('to', null, array('url'=> array('program' => $programUrl)));?></th>
			<th><?php echo $this->Paginator->sort('content', null, array('url'=> array('program' => $programUrl)));?></th>
			<th><?php echo $this->Paginator->sort('fixed-time', __('Date'), array('url'=> array('program' => $programUrl)));?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	foreach ($unattachedMessages as $unattachedMessage): ?>
	<tr>
		<td><?php echo h($unattachedMessage['UnattachedMessage']['name']); ?>&nbsp;</td>
		<td><?php echo h($unattachedMessage['UnattachedMessage']['to']); ?>&nbsp;</td>
		<td><?php echo h($unattachedMessage['UnattachedMessage']['content']); ?>&nbsp;</td>
		<td><?php echo $this->Time->format('d/m/Y H:i:s', $unattachedMessage['UnattachedMessage']['fixed-time']); ?>&nbsp;</td>
		<td class="actions">
			<?php
			     $now = new DateTime('now');
				 date_timezone_set($now,timezone_open($programTimezone));      
				 $messageDate = new DateTime($unattachedMessage['UnattachedMessage']['fixed-time'], new DateTimeZone($programTimezone));
				 if ($now < $messageDate){    
				     echo $this->Html->link(__('Edit'), array('program'=>$programUrl, 'action' => 'edit', $unattachedMessage['UnattachedMessage']['_id']));
				 } 
			?>
			<?php echo $this->Form->postLink(__('Delete'), array('program'=>$programUrl, 'action' => 'delete', $unattachedMessage['UnattachedMessage']['_id']), null,
			                                __('Are you sure you want to delete the separate message "%s" ?', $unattachedMessage['UnattachedMessage']['name'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	</div>
	
	<div class="paging">
	<?php
	    echo "<span class='ttc-page-count'>";
	    echo $this->Paginator->counter(array(
	        'format' => __('{:start} - {:end} of {:count}')
	    ));
	    echo "</span>";
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		//echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
</div>
