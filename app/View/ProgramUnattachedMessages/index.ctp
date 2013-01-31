<div class="unattached_messages index">
    <ul class="ttc-actions">
		<li><?php echo $this->Html->link(__('New Separate Message'), array('program'=>$programUrl, 'action' => 'add'), array('class' => 'ttc-button')); ?></li>
	</ul>
	<h3><?php echo __('Separate Messages');?></h3>
	<div class="ttc-data-control">
    <div id="data-control-nav" class="ttc-paging paging">
    <?php
	    echo "<span class='ttc-page-count'>";
	    echo $this->Paginator->counter(array(
	        'format' => __('{:start} - {:end} of {:count}')
	    ));
	    echo "</span>";
		echo $this->Paginator->prev('<', array('url'=> array('program' => $programUrl, '?' => $this->params['url'])), null, array('class' => 'prev disabled'));
		//echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(' >', array('url'=> array('program' => $programUrl, '?' => $this->params['url'])), null, array('class' => 'next disabled'));
	?>
	</div>
	</div>
	<div class="ttc-display-area"> 
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('name', null, array('url'=> array('program' => $programUrl)));?></th>
			<th><?php echo $this->Paginator->sort('to', __("Send To"), array('url'=> array('program' => $programUrl)));?></th>
			<th><?php echo $this->Paginator->sort('content', null, array('url'=> array('program' => $programUrl)));?></th>
			<th><?php echo $this->Paginator->sort('fixed-time', __('Time'), array('url'=> array('program' => $programUrl)));?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	foreach ($unattachedMessages as $unattachedMessage): ?>
	<tr>
		<td><?php echo $unattachedMessage['UnattachedMessage']['name']; ?>&nbsp;</td>
		<td><?php
		if ($unattachedMessage['UnattachedMessage']['model-version'] <= 2) {
		    if (is_array($unattachedMessage['UnattachedMessage']['to'])) {
		        echo implode($unattachedMessage['UnattachedMessage']['to'], "<br/>");
		    } else {
		        echo $unattachedMessage['UnattachedMessage']['to'];
		    }
		} else {
		    if ($unattachedMessage['UnattachedMessage']['send-to-type'] == 'all') {
		        echo __('All participants');
		    } else {
		        echo __('participant matching %s of the following tag(s)/label(s): ', 
		                $unattachedMessage['UnattachedMessage']['send-to-match-operator']);
		        foreach($unattachedMessage['UnattachedMessage']['send-to-match-conditions'] as $condition) {
		            echo $condition;
		        }
		    } 
		}
		    ?>&nbsp;</td>
		<td><?php echo $unattachedMessage['UnattachedMessage']['content']; ?>&nbsp;</td>
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
</div>
