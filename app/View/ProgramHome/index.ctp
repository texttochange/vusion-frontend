<div>
  <div class='Program Home index'>
	<div class='ttc-info'>
	<h3><?php echo __('Sending Next');?></h3>
	<table cellpadding="0" cellspacing="0">
		<tr>
			<th><?php echo __('at');?></th>
			<th><?php echo __('type');?></th>
			<th><?php echo __('to');?></th>	
			<th><?php echo __('content');?></th>
		</tr>
	<?php
	foreach ($schedules as $schedule): ?>
	<tr>
		<td><?php echo $this->Time->format('d/m/Y H:i', $schedule['date-time']); ?>&nbsp;</td>
		<?php if (isset($schedule['dialogue-id'])) { ?>
		<td>Script</td>
		<?php } elseif (isset($schedule['unattach-id'])) { ?>
		<td>Unattached</td>   
		<?php } else { ?>
		<td></td>
		<?php } ?>
		<td><?php echo h($schedule['csum']); echo __(" participant(s)"); ?>&nbsp;</td>
		<td>&quot;<?php echo h($schedule['content']); ?>&quot;&nbsp;</td>
	</tr>
	<?php endforeach; ?>
	</table>
	</div>
	
  </div>
	
<?php echo $this->Js->writeBuffer(); ?>
