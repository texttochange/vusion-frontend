<div class="unattached_messages index">
   <?php
       $contentTitle           = __('Separate Messages'); 
       $contentActions         = array();
       $containsDataControlNav = true;
       $controller             = 'programUnattachedMessages';
       
       $contentActions[] = $this->Html->link(__('+ New Separate Message'),
           array('program'=>$programDetails['url'],
               'controller' => $controller,
               'action' => 'add'),
           array('class' => 'ttc-button'));
       
       echo $this->element('header_content', compact('contentTitle', 'contentActions', 'containsDataControlNav', 'controller'));
    ?>
    <div class="ttc-table-display-area">
	<div class="ttc-table-scrolling-area display-height-size">
	<table class="unattached-messages" cellpadding="0" cellspacing="0">
	<?php $userGroupName = $this->Session->read('groupName');?>
	<thead>
	    <tr>
	        <th class="name"><?php echo $this->Paginator->sort('name', null, array('url'=> array('program' => $programDetails['url'])));?></th>
	        <th class="send-to"><?php echo $this->Paginator->sort('to', __("Send To"), array('url'=> array('program' => $programDetails['url'])));?></th>
	        <th class="content"><?php echo $this->Paginator->sort('content', null, array('url'=> array('program' => $programDetails['url'])));?></th>
	        <th class="delivery"><?php echo _('Delivery');?></th>
	        <th class="date-time"><?php echo $this->Paginator->sort('fixed-time', __('Time'), array('url'=> array('program' => $programDetails['url'])));?></th>
	        <th class="creator"><?php echo $this->Paginator->sort('created-by', __('Creator'), array('url'=> array('program' => $programDetails['url'])));?></th>
	        <th class="action" class="actions"><?php echo __('Actions');?></th>
	    </tr>
	    </thead>
	    <tbody>
	        <?php
	        foreach ($unattachedMessages as $unattachedMessage): ?>
	    <tr>
	        <td class="direction"><?php echo $unattachedMessage['UnattachedMessage']['name']; ?>&nbsp;</td>
	        <td class="send-to"><?php
	            if (in_array($unattachedMessage['UnattachedMessage']['model-version'], array('1', '2'))) {
	                if (is_array($unattachedMessage['UnattachedMessage']['to'])) {
	                    echo implode($unattachedMessage['UnattachedMessage']['to'], "<br/>");
    	                } else {
    	                    echo $unattachedMessage['UnattachedMessage']['to'];
    	                }
    	        } else {
    	            switch ($unattachedMessage['UnattachedMessage']['send-to-type']) {
    	            case "all":
    	                echo __('All participants');
    	                break;
    	            case "match":
    	                echo __('Participant(s) matching %s of the following tag(s)/label(s): ', 
    	                    $unattachedMessage['UnattachedMessage']['send-to-match-operator']);
    	                echo implode(" - ", $unattachedMessage['UnattachedMessage']['send-to-match-conditions']);
    	                break;
    	            case "phone":
    	                echo __('%s Participant(s)', count($unattachedMessage['UnattachedMessage']['send-to-phone'])); 
    	                break;
    	            } 
    	            if (isset($unattachedMessage['UnattachedMessage']['count-schedule'])) {
    	                echo " (".$unattachedMessage['UnattachedMessage']['count-schedule'].")";
    	            }
    	        }
    	        ?>&nbsp;</td>
    	    <td class="content"><?php echo $unattachedMessage['UnattachedMessage']['content']; ?>&nbsp;</td>
    	        <?php    
                if ($unattachedMessage['UnattachedMessage']['type-schedule'] === 'none') {
                    echo '<td class="delivery"><em><b>' .  __("drafted") . '</b></em></td>';
    	        } else if (isset($unattachedMessage['UnattachedMessage']['count-schedule'])) {
    	            echo '<td class="delivery"><em><b>' .  __("%s scheduled", $unattachedMessage['UnattachedMessage']['count-schedule']) . '</b></em></td>';
    	        } else if (isset($unattachedMessage['UnattachedMessage']['count-no-credit'])) {
    	            echo '<td class="delivery"><em><b>' .  __("no credit") . '<br/>' . __("none sent") . '</b></em></td>';
    	        } else {
    	            $sumDeliveredAndAckCount = $unattachedMessage['UnattachedMessage']['count-delivered'] + $unattachedMessage['UnattachedMessage']['count-ack'];	            
    	            $sumFailedAndNackCount   = $unattachedMessage['UnattachedMessage']['count-failed'] + $unattachedMessage['UnattachedMessage']['count-nack'];
    	            
    	            if (in_array($userGroupName, array('partner manager', 'partner'))) {
    	                echo '<td class="delivery" title="' . __('AllSent(Delivered/Pending/Failed)') .'">';
    	                echo $unattachedMessage['UnattachedMessage']['count-sent'];
    	                echo '('; 
    	                echo '<span style="color:#3B8230">' . $sumDeliveredAndAckCount . '</span>';
    	                echo '/';
    	                echo '<span style="color:#FF8C0F">' . $unattachedMessage['UnattachedMessage']['count-pending'] . '</span>';
    	                echo '/';
    	                echo '<span style="color:#C43C35">' . $sumFailedAndNackCount . '</span>';
    	                echo ')';
    	                echo '</td>';
    	            } else {
    	                echo '<td class="delivery" title="' . __('AllSent(Delivered/Pending/Failed - Ack/Nack)') .'">';
    	                echo $unattachedMessage['UnattachedMessage']['count-sent'];
    	                echo '(';
    	                echo '<span style="color:#3B8230">' . $unattachedMessage['UnattachedMessage']['count-delivered'] . '</span>';
    	                echo '/';
    	                echo '<span style="color:#FF8C0F">' . $unattachedMessage['UnattachedMessage']['count-pending'] . '</span>';
    	                echo '/';
    	                echo '<span style="color:#C43C35">' . $unattachedMessage['UnattachedMessage']['count-failed'] . '</span>';
    	                echo '&nbsp -';
    	                echo '<span style="color:#3B8230">' . $unattachedMessage['UnattachedMessage']['count-ack'] . '</span>';
    	                echo '/';
    	                echo '<span style="color:#C43C35">' . $unattachedMessage['UnattachedMessage']['count-nack'] . '</span>';
    	                echo ')';
    	                echo '</td>';
    	            }
    	        }
    	        ?>
    	    <td class="date-time">
                <?php
                if ($unattachedMessage['UnattachedMessage']['type-schedule'] != 'none') { 
                    echo $this->Time->format('d/m/Y H:i:s', $unattachedMessage['UnattachedMessage']['fixed-time']); 
                } else {
                    echo __("None");
                }
                ?>&nbsp;</td>
    	    <td id="direction-css"><?php echo $unattachedMessage['UnattachedMessage']['created-by']; ?>&nbsp;</th>
    	    <td class="action actions">
     	        <?php
                if ($unattachedMessage['UnattachedMessage']['type-schedule'] === 'none') {
                    $editButton = true;
                } else {
        	        $now = new DateTime('now');
        	        date_timezone_set($now,timezone_open($programDetails['settings']['timezone']));      
        	        $messageDate = new DateTime($unattachedMessage['UnattachedMessage']['fixed-time'], new DateTimeZone($programDetails['settings']['timezone']));
    	            if ($now < $messageDate) {
                        $editButton = true;
                    }
                }
                if (isset($editButton)) { 
    	            echo $this->Html->link(
                        __('Edit'), 
                        array('program'=>$programDetails['url'], 'action' => 'edit', $unattachedMessage['UnattachedMessage']['_id']));
    	        }
    	        echo $this->Form->postLink(
                    __('Delete'), 
                    array('program'=>$programDetails['url'], 'action' => 'delete', $unattachedMessage['UnattachedMessage']['_id']),
                    null,
    	            __('Are you sure you want to delete the separate message "%s" ?', $unattachedMessage['UnattachedMessage']['name'])); ?>
    	       </td>		
    	       </tr>
    	       <?php endforeach; ?>
    	</tbody>
    </table>
    </div>
    </div>
</div>
