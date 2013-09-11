<div class="status index">    
    <ul class="ttc-actions">
        <li>
        <?php
        if (!isset($urlParams)) {
            $urlParams = "";
        }
        echo $this->AclLink->generatePostLink(
                __('Delete'),
                $programDetails['url'], 
                'programHistory',
                'delete', 
                __('Are you sure you want to delete %s histories?', $this->Paginator->counter(array(
                    'format' => __('{:count}')))),
                array('class' => 'ttc-button'),
                null,
                $urlParams); 
        ?>
        </li>
		<li>
		    <?php
		        $exportUrl = $this->Html->url(array('program' =>$programDetails['url'], 'controller' => 'programHistory', 'action'=>'export'));
                echo $this->Html->tag(
                    'span', 
                    __('Export'), 
                    array('class' => 'ttc-button', 'name' => 'export', 'url' => $exportUrl)); 
                $this->Js->get('[name=export]')->event('click',
                    'generateExportDialogue(this);');
		    ?>
		</li>
		<li><?php
		echo $this->Html->tag(
		    'span', 
		    __('Filter'), 
		    array('class' => 'ttc-button', 'name' => 'add-filter')); 
		$this->Js->get('[name=add-filter]')->event(
		    'click',
		    '$("#advanced_filter_form").show();
		    createFilter();
		    addStackFilter();');
		?></li>
	</ul>
    <h3><?php echo __('Program History'); ?></h3>
    <?php	
    echo $this->element('filter_box', array(
        'controller' => 'programHistory'));
	?>
	<div class="ttc-table-display-area">
	<div  class="ttc-table-scrolling-area display-height-size">
	<table  class="histories" cellpadding="0" cellspacing="0">
	    <thead >
	        <tr>                                                                        
			    <th class="phone"><?php echo $this->Paginator->sort('participant-phone', __('Phone'), array('url'=> array('program' => $programDetails['url'], '?'=>$this->params['url'])));?></th>
			    <th class="direction"><?php echo $this->Paginator->sort('message-direction', __('Direction'), array('url'=> array('program' => $programDetails['url'], '?'=>$this->params['url'])));?></th>
			    <th class="status"><?php echo $this->Paginator->sort('message-status', __('Status'), array('url'=> array('program' => $programDetails['url'], '?'=>$this->params['url'])));?></th>
			    <th class="details2"><?php echo $this->Paginator->sort('message-content', __('Details'), array('url'=> array('program' => $programDetails['url'], '?'=>$this->params['url'])));?></th>
			    <th class="date-time"><?php echo $this->Paginator->sort('timestamp', __('Time'), array('url'=> array('program' => $programDetails['url'], '?'=>$this->params['url'])));?></th>
			</tr>
		</thead>
		<tbody>
			<?php if (preg_grep('/^filter/', array_keys($this->params['url'])) && $statuses == null) { ?>
			<tr>
	            <td colspan=6><?php echo __("No results found.") ?></td>
	        </tr>
	            <?php } else {?>    
	                <?php
	                foreach ($statuses as $history): ?>
	        <tr>
	            <td class="phone"><?php echo $history['History']['participant-phone']; ?></td>
	            <td class="direction"><?php echo ucfirst($history['History']['message-direction']); ?></td>
	            <?php
	            $status = "&nbsp;";
	            $title = null;
	            if (isset($history['History']['message-status'])) { 
	                $status = $history['History']['message-status'];
	                switch ($status) {
	                case 'failed': 
	                    $title = $history['History']['failure-reason'];
	                    break;
	                case 'forwarded':
	                    $tmp=array();
	                    foreach ($history['History']['forwards'] as $forward) {
	                        $timestamp = $this->Time->format('d/m/Y H:i:s', $forward['timestamp']);
	                        if ($forward['status'] == 'failed') {
	                            $reason = str_replace('"',"'",$forward['failure-reason']);
	                            $tmp[] = __("forward is %s reason %s at %s by %s", $forward['status'], $reason, $timestamp, $forward['to-addr']);
	                        } else {
	                            $tmp[] = __("forward is %s at %s by %s", $forward['status'], $timestamp, $forward['to-addr']);
	                        }
	                    }
	                    $title = implode("&#013;", $tmp);
	                    break;
	                case 'received':
	                    $status = "&nbsp;";
	                    break;
	                case 'missing-data':
	                    $title = $history['History']['missing-data'][0];
	                    break;
	                }
	            }
	            echo '<td class="status" '. (isset($title)? 'title="' . $title . '"' : '') . '>'. $status.'</td>';
	            ?>
	            <td class="details"><?php echo $history['History']['message-content']; ?>&nbsp;</td>
	            <td class="date-time"><?php echo $this->Time->format('d/m/Y H:i:s', $history['History']['timestamp']); ?>&nbsp;</td>
	        </tr>
	        <?php endforeach; ?>
	            <?php } ?>
	     </tbody>
	</table>
	</div>
	</div>
</div>	

<?php echo $this->Js->writeBuffer(); ?>
