<div class="unmatchable replies index">
    <ul class="ttc-actions">
        <li>
        <?php 
        echo $this->Html->tag(
            'span', 
            __('Filter'), 
            array('class' => 'ttc-button', 'name' => 'add-filter')); 
        $this->Js->get('[name=add-filter]')->event(
            'click',
            '$("#advanced_filter_form").show();
            createFilter();
            addStackFilter();');
		?> 
		</li>
	</ul>
	<h3><?php echo __('Unmatchable Replies');?></h3>
	<?php
	    echo $this->element('filter_box', array(
	        'controller' => 'unmatchableReply'));
	?>
	<table cellpadding="0" cellspacing="0">
	<tr>                                                                        
			<th><?php echo $this->Paginator->sort('participant-phone', __('From'));?></th>
			<th><?php echo $this->Paginator->sort('to', __('To'));?></th>
			<th><?php echo $this->Paginator->sort('message-content', __('Message'));?></th>
			<th><?php echo $this->Paginator->sort('timestamp', __('Time'));?></th>
	</tr>
	<?php
	    foreach($unmatchableReplies as $unmatchableReply):
	?>
	<tr>
		<td><?php echo h($unmatchableReply['UnmatchableReply']['participant-phone']); ?>&nbsp;</td>
		<td><?php echo h($unmatchableReply['UnmatchableReply']['to']); ?>&nbsp;</td>
		<td><?php echo h($unmatchableReply['UnmatchableReply']['message-content']); ?>&nbsp;</td>
		<td><?php echo $this->Time->format('d/m/Y h:i', $unmatchableReply['UnmatchableReply']['timestamp']); ?>&nbsp;</td>
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
	    <li><?php echo $this->Html->link(__('Back To Program List'),
			array('controller' => 'programs', 
                        'action' => 'index'));
            ?></li>
	</ul>
</div>
