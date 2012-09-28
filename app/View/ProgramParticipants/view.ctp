<div class="participant view">
    <?php if ($this->Session->read('Auth.User.group_id') != 4) { ?>
    <ul class="ttc-actions">
		<li><?php echo $this->Html->link(__('Edit Participant'), array('program'=>$programUrl, 'action' => 'edit', $participant['Participant']['_id'])); ?> </li>
	</ul>
	<?php } ?>
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
		<dt><?php echo __('Enrolled'); ?></dt>
		<dd><?php 
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
		<dt><?php echo __('Profile'); ?></dt>
		<dd><?php
		if (count($participant['Participant']['profile']) > 0) {
	        foreach ($participant['Participant']['profile'] as $label => $value) {
                echo $this->Html->tag('div', __("%s: %s", $label, $value));
            }
         } else {
		    echo "&nbsp;"; 
         }
		?></dd>
	</dl>
	<br/>
			<h3><?php echo __("Participant's Scheduled Messages"); ?></h3>
	
			<table cellpadding="0" cellspacing="0">
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
			<?php if (isset($schedule['Schedule']['dialogue-id'])) { 
			    echo $this->Html->tag('td', __('Dialogue'));
			} elseif (isset($schedule['Schedule']['unattach-id'])) {
			    echo $this->Html->tag('td', __('Separate Msg'));   
			} else { ?>
			    <td></td>
			<?php } ?>
			<td><?php
			    $objectType = str_replace("-schedule", "", $schedule['Schedule']['object-type']);
			    echo __(str_replace("dialogue", "message", $objectType)); ?></td>
			<td><?php echo __($schedule['Schedule']['content']); ?></td>
			</tr>
			<?php endforeach; ?>
			</table>
	<br/>
			<h3><?php echo __("Participant's History"); ?></h3>
	
			<table cellpadding="0" cellspacing="0">
			<tr>
				<th><?php echo __('time');?></th>
				<th><?php echo __('direction');?></th>
				<th><?php echo __('message');?></th>
			</tr>
			<?php
			foreach ($histories as $history): ?>
			
			<tr>
			<td>
			    <?php echo $this->Time->format('d/m/Y H:i:s', $history['History']['timestamp']); ?>&nbsp;</td>
			    <td><?php echo h($history['History']['message-direction']); ?>&nbsp;</td>
			    <?php if (isset($history['History']['content'])) { ?>
			    <td><?php echo h($history['History']['content']); ?>&nbsp;</td>
			    <?php } else { ?>
			    <td><?php echo h($history['History']['message-content']); ?>&nbsp;</td>
			    <?php } ?>
			</tr>
			<?php endforeach; ?>
			</table>

</div>


