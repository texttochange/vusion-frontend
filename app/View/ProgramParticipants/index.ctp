<div class="participants index">
    <ul class="ttc-actions">
        <li>
       <?php
        if (!isset($urlParams)) {
            $urlParams = "";
        }
        echo $this->AclLink->generatePostLink(
                __('Delete Participants'),
                $programUrl, 
                'programParticipants',
                'massDelete', 
                __('Are you sure you want to delete %s participants?', $this->Paginator->counter(array(
                    'format' => __('{:count}')))),
                array('class' => 'ttc-button'),
                null,
                $urlParams); 
        ?>
        </li>
		<li><?php echo $this->AclLink->generateButton(
		                    __('Add Participant'), 
		                    $programUrl,
		                    'programParticipants',
		                    'add',
		                    array('class' => 'ttc-button')); ?></li>
		<li><?php echo $this->AclLink->generateButton(
		                     __('Import Participant(s)'), 
		                     $programUrl,
		                     'programParticipants',
		                     'import',
		                     array('class' => 'ttc-button')); ?></li>
		<li><?php echo $this->Html->tag(
		                'span', 
		                __('Add Filter'), 
		                array('class' => 'ttc-button', 'name' => 'add-filter')); 
		          $this->Js->get('[name=add-filter]')->event('click',
		              '$("#advanced_filter_form").show();
		              addStackFilter();');
		?> </li>
	</ul>
	<h3><?php echo __('Participants'); ?></h3>
	<?php print_r($participants[3]['Participant']['enrolled']);
	   $this->Js->set('myOptions', $filterFieldOptions);
	   $this->Js->set('dialogueConditionOptions', $filterDialogueConditionsOptions);
	   echo $this->Form->create('Participant', array('type'=>'get', 
	                                               'url'=>array('program'=>$programUrl, 'controller' => 'programParticipants', 'action'=>'index'), 
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
	<div class="ttc-display-area">
	<table cellpadding="0" cellspacing="0">
	<tr>
	    <th><?php echo $this->Paginator->sort('phone', null, array('url'=> array('program' => $programUrl))); ?></th>
	    <th><?php echo $this->Paginator->sort('last-optin-date', __('Last Optin Date'), array('url'=> array('program' => $programUrl))); ?></th>
	    <th><?php echo $this->Paginator->sort('last-optout-date', __('Last Optout Date'), array('url'=> array('program' => $programUrl))); ?></th>
	    <th><?php echo $this->Paginator->sort('enrolled', null, array('url'=> array('program' => $programUrl))); ?></th> 
	    <th><?php echo $this->Paginator->sort('tags', null, array('url'=> array('program' => $programUrl))); ?></th>
	    <th><?php echo $this->Paginator->sort('profile', null, array('url'=> array('program' => $programUrl))); ?></th>
	<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php if (preg_grep('/^filter/', array_keys($this->params['url'])) && $participants == null) { ?>
	    <tr>
	        <td colspan=7><?php echo __("No results found.") ?></td>
	    </tr>
	<?php } else {?>   
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
	    if (isset($participant['Participant']['last-optout-date'])) {
	        echo $this->Time->format('d/m/Y H:i:s', $participant['Participant']['last-optout-date']); 
	    } else {
	        echo $this->Html->tag('div', ''); 
	    }
	    ?></td>  
	    <td><?php
  	    if (count($participant['Participant']['enrolled']) > 0) {
  	        foreach ($participant['Participant']['enrolled'] as $enrolled) {
  	            foreach ($dialogues as $dialogue) {
  	                if ($dialogue['dialogue-id'] == $enrolled['dialogue-id']) {
  	                    echo $this->Html->tag('div', __("%s at %s", $dialogue['Active']['name'], $this->Time->format('d/m/Y H:i:s', $enrolled['date-time'])));
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
	        foreach ($participant['Participant']['profile'] as $profileItem) {
                echo $this->Html->tag('div', __("%s: %s", $profileItem['label'], $profileItem['value']));
            }
         } else {
            echo $this->Html->tag('div', ''); 
         }
        ?></td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('program' => $programUrl, 'controller' => 'programParticipants', 'action' => 'view', $participant['Participant']['_id'])); ?>
			<?php if ($this->Session->read('Auth.User.group_id') != 4 ) { ?>
			<?php echo $this->Html->link(__('Edit'), array('program' => $programUrl, 'controller' => 'programParticipants', 'action' => 'edit', $participant['Participant']['_id'])); ?>
			<?php 
			     $queryParams = $this->params['url'] + array( 'current_page' => $this->Paginator->counter(array('format' => '{:page}')));
			     echo $this->Form->postLink(
			        __('Delete'), 
			        array('program' => $programUrl,
			            'controller' => 'programParticipants',
			            'action' => 'delete',
			            $participant['Participant']['_id'],
			            '?' => $queryParams),
			        null,
			        __('Are you sure you want to delete participant %s ?', $participant['Participant']['phone'])); ?>
			<?php } ?>
		</td>
	</tr>
    <?php endforeach; ?>
    <?php } ?>
	</table>
	</div>

	<div class="paging">
	<?php
	    echo "<span class='ttc-page-count'>";
	    echo $this->Paginator->counter(array(
	        'format' => __('{:start} - {:end} of {:count}')
	    ));
	    echo "</span>";
		echo $this->Paginator->prev('< ' . __('previous'), array('url'=> array('program' => $programUrl, '?' => $this->params['url'])), null, array('class' => 'prev disabled'));
		//echo $this->Paginator->numbers(array('separator' => '', 'url'=> array('program' => $programUrl)));
		echo $this->Paginator->next(__('next') . ' >', array('url'=> array('program' => $programUrl, '?' => $this->params['url'])), null, array('class' => 'next disabled'));
	?>
	</div>
	
</div>
