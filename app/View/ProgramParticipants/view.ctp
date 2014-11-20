<div class="participant view width-size">
    <ul class="ttc-actions">
        <li>
        <?php 
		    echo $this->AclLink->generatePostLink(
		        __('Delete Participant and Clear History'),
		        $programDetails['url'],
		        'programParticipants',
		        'delete',
		         __('Are you sure you want to delete the participant %s and all his histories?', $participant['Participant']['phone']),
		        array('class'=>'ttc-button'),
		        $participant['Participant']['_id'],
		        array('include'=>'history'));
	    ?>
        </li>
		<li><?php 
		    echo $this->AclLink->generateButton(
		        __('Edit Participant'),
		        $programDetails['url'],
		        'programParticipants',
		        'edit',
		        array('class'=>'ttc-button'),
		        $participant['Participant']['_id']);
		    ?> 
		</li>		
		<li><?php 
		    echo $this->AclLink->generatePostLink(
		        __('Reset'),
		        $programDetails['url'],
		        'programParticipants',
		        'reset',
		         __('Are you sure you want to reset the participant %s?', $participant['Participant']['phone']),
		        array('class'=>'ttc-button'),
		        $participant['Participant']['_id']);
		    ?> 
		</li>
		<li><?php
		    if ($participant['Participant']['session-id'] != null) {
		        echo $this->AclLink->generatePostLink(
		            __('Optout'),
		            $programDetails['url'],
		            'programParticipants',
		            'optout',
		            __('Are you sure you want to optout the participant %s?', $participant['Participant']['phone']),
		            array('class'=>'ttc-button'),
		            $participant['Participant']['_id']);
		    } else {
		        echo $this->AclLink->generatePostLink(
		            __('Optin'),
		            $programDetails['url'],
		            'programParticipants',
		            'optin',
		            __('Are you sure you want to optin the participant %s?', $participant['Participant']['phone']),
		            array('class'=>'ttc-button'),
		            $participant['Participant']['_id']);
		    }
		    ?> 
		</li>
	</ul>
    <h3><?php echo __('Participant'); ?></h3>
	<dl>
		<dt><?php echo __('Phone'); ?></dt>
		<dd><?php echo $participant['Participant']['phone']; ?>
		</dd>
		<dt><?php echo __('Last Optin Date'); ?></dt>
		<dd><?php 
			if ($participant['Participant']['last-optin-date']) {
			    echo $this->Time->format('d/m/Y H:i:s', $participant['Participant']['last-optin-date']); 
			} else {
			    echo "&nbsp;"; 
			}?>
		</dd>
		<dt><?php echo __('Last Optout Date'); ?></dt>
		<dd><?php 
			if (isset($participant['Participant']['last-optout-date'])) {
			    echo $this->Time->format('d/m/Y H:i:s', $participant['Participant']['last-optout-date']); 
			} else {
			    echo "&nbsp;"; 
			}?>
		</dd>
		<dt><?php echo __('Enrolled'); ?></dt>
		<dd><?php 
		if (count($participant['Participant']['enrolled']) > 0) {
		    foreach ($participant['Participant']['enrolled'] as $enrolled) {
		        foreach ($currentProgramData['dialogues'] as $dialogue) {
		            if ($dialogue['dialogue-id'] == $enrolled['dialogue-id']) {
  	                    echo $this->Html->tag('div', __("%s at %s", $dialogue['Active']['name'], $this->Time->format('d/m/Y H:i:s', $enrolled['date-time'])));
  	                    break;
  	                }
		        }
		    }
		} else {
		    echo "&nbsp;"; 
		}?></dd>
		<dt><?php echo __('Tags'); ?></dt>
		<dd><?php 
		if (count($participant['Participant']['tags']) > 0) {
	        foreach ($participant['Participant']['tags'] as $tag) {
	            echo $this->Html->tag('div', __("%s", $tag));
	        }
        } else {
		    echo "&nbsp;"; 
        }
		?></dd>
		<dt><?php echo __('Labels'); ?></dt>
		<dd><?php
		if (count($participant['Participant']['profile']) > 0) {
	        foreach ($participant['Participant']['profile'] as $profileItem) {
                echo $this->Html->tag('div', __("%s: %s", $profileItem['label'], $profileItem['value']));
            }
         } else {
		    echo "&nbsp;"; 
         }
		?></dd>
	</dl>
	<br/>
			<h3><?php echo __("Participant's Scheduled Messages"); ?></h3>
			<table class="ttc-table-subelement" cellpadding="0" cellspacing="0">
			<tr>
				<th><?php echo __('Time');?></th>
				<th><?php echo __('Source');?></th>
				<th><?php echo __('Type');?></th>
				<th><?php echo __('Details');?></th>
			</tr>
			<?php
			foreach ($schedules as $schedule): ?>
			<tr>
			<td><?php echo $this->Time->format('d/m/Y H:i', $schedule['Schedule']['date-time']); ?>&nbsp;</td>
			<?php if (isset($schedule['Schedule']['dialogue-id']) or isset($schedule['Schedule']['context']['dialogue-id']) ) { 
			    echo $this->Html->tag('td', __('Dialogue'));
			} elseif (isset($schedule['Schedule']['unattach-id'])) {
			    echo $this->Html->tag('td', __('Separate Msg'));   
			} else { ?>
			    <td></td>
			<?php } ?>
			<td><?php
			    $objectType = str_replace("-schedule", "", $schedule['Schedule']['object-type']);
			    echo str_replace("dialogue", "message", $objectType); ?></td>
			<td><?php echo $schedule['Schedule']['content']; ?></td>
			</tr>
			<?php endforeach; ?>
			</table>
	<br/>
			<h3><?php echo __("Participant's History"); ?></h3>
			<table class="ttc-table-subelement" cellpadding="0" cellspacing="0">
			<tr>
				<th><?php echo __('Time');?></th>
				<th><?php echo __('Type');?></th>
				<th><?php echo __('Status');?></th>
				<th><?php echo __('Details');?></th>
			</tr>
			<?php
			foreach ($histories as $history): ?>
			<tr>
			<td>
			    <?php echo $this->Time->format('d/m/Y H:i:s', $history['History']['timestamp']); ?>&nbsp;</td>
			    <?php 
			        $messageType = array('dialogue-history', 'request-history', 'unattach-history', 'unmatching-history');
			        $markerType = array('datepassed-marker-history', 'oneway-marker-history', 'datepassed-action-marker-history');
			        if (!isset($history['History']['object-type']) || in_array($history['History']['object-type'], $messageType)) { ?>
			             <td><?php echo ucfirst($history['History']['message-direction']    ); ?>&nbsp;</td>
			             <td><?php 
			             if (isset($history['History']['message-status'])) {
			                     echo $history['History']['message-status'];
			                 } 
			             ?>&nbsp;</td>
	 		             <?php if (isset($history['History']['content'])) { ?>
	 		                 <td><?php echo $history['History']['content']; ?>&nbsp;</td>
	 		             <?php } else { ?>
	 		                 <td><?php echo $history['History']['message-content']; ?>&nbsp;</td>
	 		             <?php }; ?>
	 		        <?php } elseif (in_array($history['History']['object-type'], $markerType)) { ?>
	 		             <td><?php echo __("Marker"); ?>&nbsp;</td>
			             <td>&nbsp;</td>
			             <td>
			             <?php if ($history['History']['object-type'] == 'oneway-marker-history') {
			                 echo __("One way marker on interaction <i>%s</i>", $history['History']['details']);
			             } elseif ($history['History']['object-type'] == 'datepassed-marker-history') {
			                 if (isset($history['History']['unattach-id'])) {
			                     echo __("Date passed marker on Separate Message <i>%s</i>", $history['History']['details']);
			                 } else {
			                     echo __("Date passed marker on interaction <i>%s</i>", $history['History']['details']);
			                 }
			             } elseif ($history['History']['object-type'] == 'datepassed-action-marker-history') {
			                 echo __("Date passed marker on action <i>%s</i> scheduled at %s", 
			                         $history['History']['action-type'], 
			                         $this->Time->format('d/m/Y H:i:s', $history['History']['scheduled-date-time'])); 
			             }?> 
			             </td>
	 		        <?php } else {?>
	 		            <td><?php echo __("Error cannot display %s", $history['History']['object-type']); ?>&nbsp;</td>
			             <td>&nbsp;</td>
			             <td>&nbsp;</td>
	 		        <?php } ?>
			</tr>
			<?php endforeach; ?>
			</table>
</div>


