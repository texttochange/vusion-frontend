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
    <div class="ttc-table-display-area">
	<div class="ttc-table-scrolling-area">
	<table  cellpadding="0" cellspacing="0">
	<thead>
	    <tr>
	        <th id="direction-css"><?php echo $this->Paginator->sort('name', null, array('url'=> array('program' => $programUrl)));?></th>
	        <th id="send-to-css"><?php echo $this->Paginator->sort('to', __("Send To"), array('url'=> array('program' => $programUrl)));?></th>
	        <th id="content-css"><?php echo $this->Paginator->sort('content', null, array('url'=> array('program' => $programUrl)));?></th>
	        <th id="delivery-css" title="<?php echo __('AllSent(Delivered/Pending/Failed - Ack/Nack)') ?>">
	            <?php echo $this->Paginator->sort( ''  ,_('Delivery'), array('url'=> array('program' => $programUrl)));?></th>
	        <th id="date-time-css"><?php echo $this->Paginator->sort('fixed-time', __('Time'), array('url'=> array('program' => $programUrl)));?></th>
	        <th id="direction-css"><?php echo $this->Paginator->sort('created-by', __('Created By'), array('url'=> array('program' => $programUrl)));?></th>
	        <th id="action-css" class="actions"><?php echo __('Actions');?></th>
	    </tr>
	    </thead>
	    <tbody>
	        <?php
	        foreach ($unattachedMessages as $unattachedMessage): ?>
	    <tr>
	        <td id="direction-css"><?php echo $unattachedMessage['UnattachedMessage']['name']; ?>&nbsp;</td>
	        <td id="send-to-css"><?php
	            if (in_array($unattachedMessage['UnattachedMessage']['model-version'], array('1', '2'))) {
	                if (is_array($unattachedMessage['UnattachedMessage']['to'])) {
	                    echo implode($unattachedMessage['UnattachedMessage']['to'], "<br/>");
    	                } else {
    	                    echo $unattachedMessage['UnattachedMessage']['to'];
    	                }
    	        } else {
    	            if ($unattachedMessage['UnattachedMessage']['send-to-type'] == 'all') {
    	                echo __('All participants');
    	            } else {
    	                echo __('Participant(s) matching %s of the following tag(s)/label(s): ', 
    	                    $unattachedMessage['UnattachedMessage']['send-to-match-operator']);
    	                echo implode(" - ", $unattachedMessage['UnattachedMessage']['send-to-match-conditions']);
        
    	            } 
    	            if (isset($unattachedMessage['UnattachedMessage']['count-schedule'])) {
    	                echo " (".$unattachedMessage['UnattachedMessage']['count-schedule'].")";
    	            }
    	        }
    	        ?>&nbsp;</td>
    	    <td id="content-css"><?php echo $unattachedMessage['UnattachedMessage']['content']; ?>&nbsp;</td>		
    	    <td id="delivery-css">
    	        <?php 
    	        if (isset($unattachedMessage['UnattachedMessage']['count-schedule'])) {
    	            echo '<em><b>' .  __("scheduled") . '</b></em>';
    	        } else {
    	            echo $unattachedMessage['UnattachedMessage']['count-sent'];
    	            echo "(";
    	            echo '<span style="color:#3B8230">' . $unattachedMessage['UnattachedMessage']['count-delivered'] . '</span>';
    	            echo "/";
    	            echo '<span style="color:#FF8C0F">' . $unattachedMessage['UnattachedMessage']['count-pending'] . '</span>';
    	            echo "/";
    	            echo '<span style="color:#C43C35">' . $unattachedMessage['UnattachedMessage']['count-failed'] . '</span>';
    	            echo "&nbsp -";
    	            echo '<span style="color:#3B8230">' . $unattachedMessage['UnattachedMessage']['count-ack'] . '</span>';
    	            echo "/";
    	            echo '<span style="color:#C43C35">' . $unattachedMessage['UnattachedMessage']['count-nack'] . '</span>';
    	            echo ")";
    	        }
    	        ?>
    	    </td>
    	    <td id="date-time-css"><?php echo $this->Time->format('d/m/Y H:i:s', $unattachedMessage['UnattachedMessage']['fixed-time']); ?>&nbsp;</td>
    	    <td id="direction-css"><?php echo $unattachedMessage['UnattachedMessage']['created-by']; ?>&nbsp;</th>
    	    <td id="action-css" class="actions">
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
    	</tbody>
    </table>
    </div>
    </div>
</div>
