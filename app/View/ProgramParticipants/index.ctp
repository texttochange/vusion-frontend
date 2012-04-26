<div>
	
<div class="participants index">
        <?php
        
        ?>
	<h3>Participants</h3>
	<table cellpadding="0" cellspacing="0">
	<tr>
	<?php
	$headers = array();
	foreach ($participants as $participant) {
	    foreach ($participant['Participant'] as $key => $value) {
	        if ($key!='modified' && $key!='created' && $key!='_id' && !in_array($key, $headers)) {
	            array_push($headers, $key); 
	            echo $this->Html->tag('th', null);
	            echo $this->Paginator->sort($key, null, array('url'=> array('program' => $programUrl)));
	        }
	     }
	}
	?>
	<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php foreach ($participants as $participant): ?>
	<tr>
	    <?php 
            foreach ($headers as $key) {
                if (isset($participant['Participant'][$key])) {
                    echo $this->Html->tag('td', $participant['Participant'][$key]);
                } else {
                    echo $this->Html->tag('td', '');
                }
            }
            ?>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('program' => $programUrl, 'controller' => 'programParticipants', 'action' => 'view', $participant['Participant']['_id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('program' => $programUrl, 'controller' => 'programParticipants', 'action' => 'edit', $participant['Participant']['_id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('program' => $programUrl, 'controller' => 'programParticipants', 'action' => 'delete', $participant['Participant']['_id']), null, __('Are you sure you want to delete # %s?', $participant['Participant']['_id'])); ?>
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
		echo $this->Paginator->prev('< ' . __('previous'), array('url'=> array('program' => $programUrl)), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => '', 'url'=> array('program' => $programUrl)));
		echo $this->Paginator->next(__('next') . ' >', array('url'=> array('program' => $programUrl)), null, array('class' => 'next disabled'));
	?>
	</div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Back To Program Home'),
	                array('program'=> $programUrl,'controller' => 'programHome'));
                ?></li>
		<li><?php echo $this->Html->link(__('Add Participant'), array('program' => $programUrl, 'controller' => 'programParticipants', 'action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('Import Participant(s)'), array('program' => $programUrl, 'controller' => 'programParticipants', 'action' => 'import')); ?></li>
		
	</ul>
</div>
</div>
