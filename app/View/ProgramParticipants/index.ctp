<div class="participants index">
	<?php
	$contentTitle = __('Participants');
	$contentActions = array();
	$containsDataControlNav = true;
	$containsFilter = true;
	$controller = 'programParticipants';
	$urlParams = (isset($urlParams) ? $urlParams : "");

	$contentActions[] = $this->AclLink->generatePostLink(
        __('Delete'),
        $programDetails['url'], 
        'programParticipants',
        'massDelete', 
        __('Are you sure you want to delete %s participants?', $this->Paginator->counter(array(
            'format' => '{:count}'))),
        array('class' => 'ttc-button'),
        null,
        $urlParams);

    $massUntagUrl = $this->Html->url(array(
    	'program' => $programDetails['url'],
    	'controller' => 'programParticipants',
    	'action' => 'massUntag'));
    $contentActions[] = $this->AclLink->generateButton(
        __('Untag'), 
        $programDetails['url'],
        'programParticipants',
        '',
        array('class' => 'ttc-button',
            'name' => 'unTag', 
        	'url' => $massUntagUrl));
    $this->Js->get('[name=unTag]')->event('click',
        'generateMassUntagDialogue(this);');

    $massTagUrl = $this->Html->url(array(
        'program' => $programDetails['url'],
        'controller' => 'programParticipants',
        'action' => 'massTag'));
    $contentActions[] = $this->AclLink->generateButton(
        __('Tag'),
        $programDetails['url'],
        'programParticipants',
        '',
        array('class' => 'ttc-button',
            'name' => 'massTag',
            'url' => $massTagUrl));
    $this->Js->get('[name=massTag]')->event('click',
        'generateMassTagDialogue(this);');

    $contentActions[] = $this->AclLink->generateButton(
        __('+ Add'), 
        $programDetails['url'],
        'programParticipants',
        'add',
        array('class' => 'ttc-button')); 

    if ($participants != null) {
        $exportUrl = array(
                'program' => $programDetails['url'],
                'controller' => 'programParticipants',
                'action' => 'export',
                '?' => $urlParams);
        if ($order != null) {
            $exportUrl['sort'] = key($order);
            $exportUrl['direction'] = $order[key($order)];
        }
        $contentActions[] = $this->AclLink->generateButtonFromUrl(
            __('Export'),
            $exportUrl,
            array('class' => 'ttc-button'));
	}

	$contentActions[] = $this->AclLink->generateButton(
        __('Import'), 
        $programDetails['url'],
        'programParticipants',
        'import',
        array('class' => 'ttc-button'));

    $contentActions[] = $this->Html->tag(
        'span',
        __('Filter'),
        array('class' => 'ttc-button', 'name' => 'add-filter'));
    $this->Js->get('[name=add-filter]')->event(
        'click',
        '$("#advanced_filter_form").show();
         createFilter();
         addStackFilter();');

	echo $this->element('header_content', 
		compact('contentTitle', 'contentActions', 'containsFilter','containsDataControlNav', 'controller'));
	
	?>
	<div class="ttc-table-display-area">
	<div class="ttc-table-scrolling-area display-height-size">
	<table class="participants" cellpadding="0" cellspacing="0">
	    <thead>
	        <tr >
	            <th class="optout-indicator"></th>
	            <th class="phone">
	            <?php echo $this->Paginator->sort('phone', null, array('url'=> array('program' => $programDetails['url'], '?'=>$this->params['url']))); ?>
	            </th>
	            <th class="opt-date"><?php echo $this->Paginator->sort('last-optin-date', __('Last Optin Date'), array('url'=> array('program' => $programDetails['url'], '?'=>$this->params['url']))); ?></th>
	            <th class="opt-date"><?php echo $this->Paginator->sort('last-optout-date', __('Last Optout Date'), array('url'=> array('program' => $programDetails['url'], '?'=>$this->params['url']))); ?></th>
	            <th class="enrolled"><?php echo $this->Paginator->sort('enrolled', __('Enrolled'), array('url'=> array('program' => $programDetails['url'], '?'=>$this->params['url']))); ?></th> 
	            <th class="tags"><?php echo $this->Paginator->sort('tags', __('Tags'), array('url'=> array('program' => $programDetails['url'], '?'=>$this->params['url']))); ?></th>
	            <th class="profile"><?php echo $this->Paginator->sort('profile',__('Labels'), array('url'=> array('program' => $programDetails['url'], '?'=>$this->params['url']))); ?></th>
	            <th class="action" class="actions"><?php echo __('Actions');?></th>
	        </tr>
	      </thead>	      
	      <tbody >
	      <?php if (preg_grep('/^filter/', array_keys($this->params['url'])) && $participants == null) { ?>
	          <tr>
	              <td colspan=7><?php echo __("No results found.") ?></td>
	          </tr>
	      <?php } else {?>   
	      <?php foreach ($participants as $participant): ?>
	      <tr class="<?php echo ((!isset($participant['Participant']['session-id'])) ? 'optout' : '');?>"
	      title="<?php echo ((!isset($participant['Participant']['session-id'])) ? 'optout' : '');?>"
	      >
	              <td><?php 
	              echo ((!isset($participant['Participant']['session-id'])) ? '<img src = "/img/stop.png"  class = "optout-logo">' : '');
	              ?>
	              </td>
	              <td><?php echo $participant['Participant']['phone']; 
	              ?></td>
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
	                          foreach ($currentProgramData['dialogues'] as $dialogue) {                	         
	                              if ($dialogue['dialogue-id'] == $enrolled['dialogue-id']) {
	                                  $dialogueEnrollName = $dialogue['Active']['name'];
	                          	      $dialogueEnrollDate = $this->Time->format('d/m/Y H:i:s', $enrolled['date-time']);
	                          	      $participantEnroll = $dialogueEnrollName . ' at ' .$dialogueEnrollDate;
	                                  echo $this->Html->tag('div', $participantEnroll, array('class'=> 'participant-truncated-enroll', 'title' => $participantEnroll)); 
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
	              <td>
	              <?php
	              if (count($participant['Participant']['profile']) > 0) {
	              		  foreach ($participant['Participant']['profile'] as $profileItem) {
	              		  		  $profileItemsLabel = $profileItem['label'];
	              		  		  $profileItemsValue = $profileItem['value'];
	              		  		  $participantProfile = $profileItemsLabel . ': ' . $profileItemsValue;	              		  		 
	              		  		  echo $this->Html->tag('div', $participantProfile, array('class'=> 'participant-tuncated-profile', 'title' => $participantProfile)); 
	              		  }
	              } else {
	              		  echo $this->Html->tag('div', ''); 
	              }	             
	              ?></td>	            
	               <td  class="action actions">
	                   <?php echo $this->Html->link(__('View'), array('program' => $programDetails['url'], 'controller' => 'programParticipants', 'action' => 'view', $participant['Participant']['_id'])); ?>
	                   <?php if ($this->Session->read('Auth.User.group_id') != 4 ) { ?>
	                       <?php echo $this->Html->link(__('Edit'), array('program' => $programDetails['url'], 'controller' => 'programParticipants', 'action' => 'edit', $participant['Participant']['_id'])); ?>
	                       <?php 
	                       $queryParams = $this->params['url'] + array( 'current_page' => $this->Paginator->counter(array('format' => '{:page}')));
	                       echo $this->Form->postLink(
	                           __('Delete'), 
	                           array('program' => $programDetails['url'],
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
	        </tbody>	        
	    </table>
	</div>
	</div>	
</div>
