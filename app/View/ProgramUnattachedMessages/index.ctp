<div class="unattached_messages index">
<ul class="ttc-actions">
<li></li>
<li><?php echo $this->Html->link(__('New Separate Message'), array('program'=>$programDetails['url'], 'action' => 'add'), array('class' => 'ttc-button')); ?></li>
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
echo $this->Paginator->prev('<', array('url'=> array('program' => $programDetails['url'], '?' => $this->params['url'])), null, array('class' => 'prev disabled'));
//echo $this->Paginator->numbers(array('separator' => ''));
echo $this->Paginator->next(' >', array('url'=> array('program' => $programDetails['url'], '?' => $this->params['url'])), null, array('class' => 'next disabled'));
?>
</div>
</div>
    <div class="ttc-table-display-area">
	<div class="ttc-table-scrolling-area display-height-size">
	<table class="unattached-messages" cellpadding="0" cellspacing="0">
	<thead>
	    <tr>
	        <th class="name"><?php echo $this->Paginator->sort('name', null, array('url'=> array('program' => $programDetails['url'])));?></th>
	        <th class="send-to"><?php echo $this->Paginator->sort('to', __("Send To"), array('url'=> array('program' => $programDetails['url'])));?></th>
	        <th class="content"><?php echo $this->Paginator->sort('content', null, array('url'=> array('program' => $programDetails['url'])));?></th>
	        <th class="delivery" title="<?php echo __('AllSent(Delivered/Pending/Failed - Ack/Nack)') ?>">
	        <?php echo $this->Paginator->sort( ''  ,_('Delivery'), array('url'=> array('program' => $programDetails['url'])));?></th>
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
    	    <td class="delivery">
    	        <?php 
    	        if (isset($unattachedMessage['UnattachedMessage']['count-schedule'])) {
    	            echo '<em><b>' .  __("%s scheduled", $unattachedMessage['UnattachedMessage']['count-schedule']) . '</b></em>';
    	        } else if (isset($unattachedMessage['UnattachedMessage']['count-no-credit'])) {
    	            echo '<em><b>' .  __("no credit") . '<br/>' . __("none sent") . '</b></em>';
    	        } else {
    	            echo $unattachedMessage['UnattachedMessage']['count-sent'];
    	            echo "(";    	            
    	            if ($isAllowedAccessToDialogues) {
    	                echo '<span style="color:#3B8230">' . $unattachedMessage['UnattachedMessage']['count-delivered'] + 1 . '</span>';
    	                echo "/";
    	                echo '<span style="color:#FF8C0F">' . $unattachedMessage['UnattachedMessage']['count-pending'] . '</span>';
    	                echo "/";
    	                echo '<span style="color:#C43C35">' . $unattachedMessage['UnattachedMessage']['count-failed'] . '</span>';
    	                echo "&nbsp -";
    	                echo '<span style="color:#3B8230">' . $unattachedMessage['UnattachedMessage']['count-ack'] . '</span>';
    	                echo "/";
    	                echo '<span style="color:#C43C35">' . $unattachedMessage['UnattachedMessage']['count-nack'] . '</span>';
    	            } else {
    	                echo '<span style="color:#3B8230">' . 
    	                $unattachedMessage['UnattachedMessage']['count-delivered'] + $unattachedMessage['UnattachedMessage']['count-ack'] . '</span>';
    	                echo "/";
    	                echo '<span style="color:#FF8C0F">' . $unattachedMessage['UnattachedMessage']['count-pending'] . '</span>';
    	                echo "/";
    	                echo '<span style="color:#C43C35">' . 
    	                $unattachedMessage['UnattachedMessage']['count-failed'] + $unattachedMessage['UnattachedMessage']['count-nack']. '</span>';    	                 	                
    	            }   	            
    	            echo ")";
    	        }
    	        ?>
    	    </td>
    	    <td class="date-time"><?php echo $this->Time->format('d/m/Y H:i:s', $unattachedMessage['UnattachedMessage']['fixed-time']); ?>&nbsp;</td>
    	    <td id="direction-css"><?php echo $unattachedMessage['UnattachedMessage']['created-by']; ?>&nbsp;</th>
    	    <td class="action actions">
     	       <?php
    	       $now = new DateTime('now');
    	       date_timezone_set($now,timezone_open($programDetails['settings']['timezone']));      
    	       $messageDate = new DateTime($unattachedMessage['UnattachedMessage']['fixed-time'], new DateTimeZone($programDetails['settings']['timezone']));
    	       if ($now < $messageDate){    
    	           echo $this->Html->link(__('Edit'), array('program'=>$programDetails['url'], 'action' => 'edit', $unattachedMessage['UnattachedMessage']['_id']));
    	       } 
    	       ?>
    	       <?php echo $this->Form->postLink(__('Delete'), array('program'=>$programDetails['url'], 'action' => 'delete', $unattachedMessage['UnattachedMessage']['_id']), null,
    	           __('Are you sure you want to delete the separate message "%s" ?', $unattachedMessage['UnattachedMessage']['name'])); ?>
    	       </td>		
    	       </tr>
    	       <?php endforeach; ?>
    	</tbody>
    </table>
    </div>
    </div>
</div>
