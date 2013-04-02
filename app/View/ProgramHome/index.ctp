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
	
	<div class="ttc-table-display-area">
	<div class="ttc-table-scrolling-area">
	<table cellpadding="0" cellspacing="0">
	    <thead>	
	        <tr>
			    <th id="date-time-css"><?php echo __('At');?></th>			   
			    <th id="send-to-css"><?php echo __('To');?></th>	
			    <th id="content2-css"><?php echo __('Content');?></th>
			    <th id="delivery-css"><?php echo __('Source');?></th>
			</tr>
		</thead>
		<tbody>
		<?php
		    foreach ($schedules as $schedule): ?>
		    <tr>
		        <td id="date-time-css"><?php echo $this->Time->format('d/m/Y H:i', $schedule['date-time']); ?>&nbsp;</td>
		            
		        <td id="send-to-css"><?php echo h($schedule['csum']); echo __(" participant(s)"); ?>&nbsp;</td>
		        <td id="content2-css">&quot;<?php echo h($schedule['content']); ?>&quot;&nbsp;</td>
		     <?php if (isset($schedule['dialogue-id'])) { 
		                echo $this->Html->tag('td', __('Dialogue'));
		            } elseif (isset($schedule['unattach-id'])) {
		                echo $this->Html->tag('td', __('Separate Msg'));   
		            } else { ?>
		        <td></td>
		            <?php } ?>
		       </tr>
		         <?php endforeach; ?>
		   </tbody>
		</table>
		</div>
		</div>
</div>
	
<?php echo $this->Js->writeBuffer(); ?>
