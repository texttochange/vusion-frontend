<div class="unmatchable replies index">
    <ul class="ttc-actions">
        <li><?php echo $this->Html->tag(
		                'span', 
		                __('Add Filter'), 
		                array('class' => 'ttc-button', 'name' => 'add-filter')); 
		          $this->Js->get('[name=add-filter]')->event('click',
		              '$("#advanced_filter_form").show();
		              addStackFilter();');
		?> </li>
	</ul>
	<h3><?php echo __('Unmatchable Replies');?></h3>
	<?php
	   $this->Js->set('myOptions', $filterFieldOptions);
	   echo $this->Form->create('UnmatchableReply', array('type'=>'get', 
	                                               'url'=>array('controller'=>'unmatchableReply', 'action'=>'index'), 
	                                               'id' => 'advanced_filter_form', 
	                                               'class' => 'ttc-advanced-filter'));
	   if (isset($this->params['url']['filter_param'])) {
	       $this->Js->get('document')->event('ready','
	           $("#quick_filter_form").hide();
	           $("#advanced_filter_form").show();
	           ');
	       $count = 1;
	       foreach ($this->params['url']['filter_param'] as $filter) {
	           $thirdParam = (isset($filter[3]) ? '$("input[name=\'filter_param['.$count.'][3]\']").val("'.$filter[3].'");' : '');
	           $this->Js->get('document')->event('ready',
	               'addStackFilter();
	               $("select[name=\'filter_param['.$count.'][1]\']").val("'.$filter[1].'").children("option[value=\''.$filter[1].'\']").click();
	               if ($("input[name=\'filter_param['.$count.'][2]\']").length > 0) {
	               $("input[name=\'filter_param['.$count.'][2]\']").val("'.(isset($filter[2])? $filter[2]:'').'");
	               '.$thirdParam.'
	               } else {
	               $("select[name=\'filter_param['.$count.'][2]\']").val("'.(isset($filter[2])? $filter[2]:'').'").children("option[value='.(isset($filter[2])? $filter[2]:'').']").click();
	               }',
	               true);
	           $count++;
	       }	  
	   }
       echo $this->Form->end(array('label' => 'Filter', 'class' => 'ttc-filter-submit'));       
       $this->Js->get('#advanced_filter_form')->event(
           'submit',
           '$(":input[value=\"\"]").attr("disabled", true);
           return true;');
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
