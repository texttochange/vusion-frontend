<div class='Program Home index'>
    <ul class="ttc-actions">
		<li><?php 
		echo $this->Html->tag('div', __('Restart Worker'), array('class'=> 'ttc-button', 'id' => 'restart-worker-button')); 
		$this->Js->get('#restart-worker-button')->event(
	           'click',
	           $this->Js->request(
	               array('program'=>$programUrl, 'action'=>'restartWorker.json'),
	               array('method' => 'GET',
                         'async' => true, 
	                     'dataExpression' => true,
	                     'success' => '$("#flashMessage").show().text(data["message"]).attr("class","message success")')));
		?></li>
	</ul>
	<h3><?php echo __('Sending Next');?></h3>
	<div class="ttc-display-area">
	<table cellpadding="0" cellspacing="0">
		<tr>
			<th><?php echo __('At');?></th>
			<th><?php echo __('Type');?></th>
			<th><?php echo __('To');?></th>	
			<th><?php echo __('Content');?></th>
		</tr>
	<?php
	foreach ($schedules as $schedule): ?>
	<tr>
		<td><?php echo $this->Time->format('d/m/Y H:i', $schedule['date-time']); ?>&nbsp;</td>
		<?php if (isset($schedule['dialogue-id'])) { 
		    echo $this->Html->tag('td', __('Dialogue'));
		} elseif (isset($schedule['unattach-id'])) {
		    echo $this->Html->tag('td', __('Separate Msg'));   
		} else { ?>
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
