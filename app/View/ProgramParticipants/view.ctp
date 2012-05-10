<div class="participant view">
    <ul class="ttc-actions">
		<li><?php echo $this->Html->link(__('Edit Participant'), array('program'=>$programUrl, 'action' => 'edit', $participant['Participant']['_id'])); ?> </li>
	</ul>
    <h3><?php echo __('Participant'); ?></h3>
	<dl>
		<dt><?php echo __('phone'); ?></dt>
		<dd>
			<?php echo h($participant['Participant']['phone']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('name'); ?></dt>
		<dd>
			<?php echo h($participant['Participant']['name']); ?>
			&nbsp;
		</dd>
		<?php 
		        foreach ($participant['Participant'] as $key => $value) {
		            if ($key!='modified' && $key!='created' && $key!='_id' && $key!='phone' && $key!='name') {
		                echo $this->Html->tag('dt', $key);
		                echo $this->Html->tag('dd', $value);
		            }
		        }
	    
		?>
	</dl>
	<br/>
			<h3><?php echo __("Participant's History"); ?></h3>
	
			<table cellpadding="0" cellspacing="0">
			<tr>
				<th><?php echo __('time');?></th>
				<th><?php echo __('type');?></th>
				<th><?php echo __('message');?></th>
			</tr>
			<?php
			foreach ($histories as $history): ?>
			
			<tr>
			<td>
			    <?php echo $this->Time->format('d/m/Y H:i:s', $history['History']['timestamp']); ?>&nbsp;</td>
			    <td><?php echo h($history['History']['message-type']); ?>&nbsp;</td>
			    <?php if (isset($history['History']['content'])) { ?>
			    <td><?php echo h($history['History']['content']); ?>&nbsp;</td>
			    <?php } else { ?>
			    <td><?php echo h($history['History']['message-content']); ?>&nbsp;</td>
			    <?php } ?>
			</tr>
			<?php endforeach; ?>
			</table>




