<?php
    $this->RequireJs->scripts(array("jquery"));
?>
<div class='Program Home index'>
    <?php
        $contentTitle           = __('Sending Next'); 
        $contentActions         = array();
        $containsDataControlNav = true;
        $controller             = 'programRequests';
        
        $contentActions[] = $this->Html->link(__('Restart Worker'),
            array('program'=>$programDetails['url'],
                'controller' => $controller),
            array('class' => 'ttc-button',
                'id' => 'restart-worker-button'));
        
        $this->Js->get('#restart-worker-button')->event(
            'click',
            $this->Js->request(
                array('program'=>$programDetails['url'], 'action'=>'restartWorker.json'),
                array('method' => 'GET',
                    'async' => true, 
                    'contentType' => 'application/json; charset=utf-8',
                    'dataType' => 'json',
                    'success' => 'showFlashMessages(data["message"], data["status"]);')));
        
        echo $this->element('header_content', compact('contentTitle', 'contentActions', 'containsDataControlNav', 'controller'));
   ?>
	<div class="ttc-table-display-area">
	<div class="ttc-table-scrolling-area display-height-size">
	<table class="schedules" cellpadding="0" cellspacing="0">
	    <thead>	
	        <tr>
			    <th class="date-time"><?php echo __('At');?></th>			   
			    <th class="send-to"><?php echo __('To');?></th>	
			    <th class="content-sending"><?php echo __('Content');?></th>
			    <th class="source"><?php echo __('Source');?></th>
			</tr>
		</thead>
		<tbody>
		<?php
		    foreach ($schedules as $schedule): ?>
		    <tr>
		        <td class="date-time"><?php echo $this->Time->format('d/m/Y H:i', $schedule['date-time']); ?>&nbsp;</td>
		            
		        <td class="send-to"><?php echo h($schedule['csum']); echo __(" participant(s)"); ?>&nbsp;</td>
		        <td class="content-sending">&quot;<?php echo h($schedule['content']); ?>&quot;&nbsp;</td>
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