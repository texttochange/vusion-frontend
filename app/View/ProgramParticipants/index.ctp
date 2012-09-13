<div class="participants index">
    <?php if ($this->Session->read('Auth.User.group_id') != 4 ) { ?>
    <ul class="ttc-actions">
		<li><?php echo $this->Html->link(__('Add Participant'), array('program' => $programUrl, 'controller' => 'programParticipants', 'action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('Import Participant(s)'), array('program' => $programUrl, 'controller' => 'programParticipants', 'action' => 'import')); ?></li>
	</ul>
	<?php } ?>
	<h3><?php echo __('Participants'); ?></h3>
	<div class="ttc-display-area">
	<table cellpadding="0" cellspacing="0">
	<tr>
	    <th><?php echo $this->Paginator->sort('phone', null, array('url'=> array('program' => $programUrl))); ?></th>
	    <th><?php echo $this->Paginator->sort('last-optin-date', __('Last Optin Date'), array('url'=> array('program' => $programUrl))); ?></th> 
	    <th><?php echo $this->Paginator->sort('enrolled', null, array('url'=> array('program' => $programUrl))); ?></th> 
	    <th><?php echo $this->Paginator->sort('tags', null, array('url'=> array('program' => $programUrl))); ?></th>
	    <th><?php echo $this->Paginator->sort('profile', null, array('url'=> array('program' => $programUrl))); ?></th>
	<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php foreach ($participants as $participant): ?>
	<tr>
	    <td><?php echo $participant['Participant']['phone']; ?></td>
	    <td><?php 
	    if ($participant['Participant']['last-optin-date']) {
	        echo $this->Time->format('d/m/Y H:i:s', $participant['Participant']['last-optin-date']); 
	    } else {
	        echo $this->Html->tag('div', ''); 
	    }
	    ?></td> 
	    <td><?php
  	    if (count($participant['Participant']['enrolled']) > 0) {
  	        foreach ($participant['Participant']['enrolled'] as $enrolled) {
  	            foreach ($dialogues as $dialogue) {
  	                if ($dialogue['dialogue-id'] == $enrolled) {
  	                    echo $this->Html->tag('div', __("%s", $dialogue['Active']['name']));
  	                    break;
  	                }
  	            }
  	        }
        } else {
            echo $this->Html->tag('div', ''); 
        }
	    ?></td> 
	    <td><?php 
	    if (count($participant['Participant']['tags']) > 0) {
	        foreach ($participant['Participant']['tags'] as $tag) {
	            echo $this->Html->tag('div', __("%s", $tag));
	        }
        } else {
            echo $this->Html->tag('div', '');
        }
	    ?></td> 
	    <td><?php 
	    if (count($participant['Participant']['profile']) > 0) {
	        foreach ($participant['Participant']['profile'] as $label => $value) {
                echo $this->Html->tag('div', __("%s: %s", $label, $value));
            }
         } else {
            echo $this->Html->tag('div', ''); 
         }
        ?></td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('program' => $programUrl, 'controller' => 'programParticipants', 'action' => 'view', $participant['Participant']['_id'])); ?>
			<?php if ($this->Session->read('Auth.User.group_id') != 4 ) { ?>
			<?php echo $this->Html->link(__('Edit'), array('program' => $programUrl, 'controller' => 'programParticipants', 'action' => 'edit', $participant['Participant']['_id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('program' => $programUrl, 'controller' => 'programParticipants', 'action' => 'delete', $participant['Participant']['_id']), null, __('Are you sure you want to delete participant %s ?', $participant['Participant']['phone'])); ?>
			<?php } ?>
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
		echo $this->Paginator->prev('< ' . __('previous'), array('url'=> array('program' => $programUrl)), null, array('class' => 'prev disabled'));
		//echo $this->Paginator->numbers(array('separator' => '', 'url'=> array('program' => $programUrl)));
		echo $this->Paginator->next(__('next') . ' >', array('url'=> array('program' => $programUrl)), null, array('class' => 'next disabled'));
	?>
	</div>
	
</div>
