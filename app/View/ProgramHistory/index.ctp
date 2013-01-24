<div class="status index">    
    <ul class="ttc-actions">
        <li>
        <?php
        if (!isset($urlParams)) {
            $urlParams = "";
        }
        echo $this->AclLink->generatePostLink(
                __('Delete'),
                $programUrl, 
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
		        echo $this->Form->create(null);
		        $exportOptions = array();
		        $exportOptions['export.csv'] = 'Export CSV'; 
		        $exportOptions['index.csv'] = 'Export Raw CSV';
		        $exportOptions['index.json'] = 'Export Json';
		        echo $this->Form->select('export',$exportOptions, array('id'=>'export-type', 'class'=> 'ttc-button', 'empty' => 'Export History...'));
		        echo $this->Form->end();
		        $url = $programUrl.'/programHistory/';
		        $filterParams = 
		        $this->Js->get('#export-type')->event('change', '
	                window.location = "http://"+window.location.host+"/'.$url.'"+$("#export-type option:selected").val()+window.location.search;	                
	            ');
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
	<div class="ttc-display-area">
	<table cellpadding="0" cellspacing="0">
	<tr>                                                                        
			<th><?php echo $this->Paginator->sort('participant-phone', __('Phone'), array('url'=> array('program' => $programUrl, '?'=>$this->params['url'])));?></th>
			<th><?php echo $this->Paginator->sort('message-direction', __('Direction'), array('url'=> array('program' => $programUrl, '?'=>$this->params['url'])));?></th>
			<th><?php echo $this->Paginator->sort('message-status', __('Status'), array('url'=> array('program' => $programUrl, '?'=>$this->params['url'])));?></th>
			<th><?php echo $this->Paginator->sort('failure-reason', __('Failure Reason'), array('url'=> array('program' => $programUrl, '?'=>$this->params['url'])));?></th>
			<th><?php echo $this->Paginator->sort('message-content', __('Details'), array('url'=> array('program' => $programUrl, '?'=>$this->params['url'])));?></th>
			<th><?php echo $this->Paginator->sort('timestamp', __('Time'), array('url'=> array('program' => $programUrl, '?'=>$this->params['url'])));?></th>
	</tr>
	<?php if (preg_grep('/^filter/', array_keys($this->params['url'])) && $statuses == null) { ?>
	    <tr>
	        <td colspan=6><?php echo __("No results found.") ?></td>
	    </tr>
	<?php } else {?>    
	<?php
	foreach ($statuses as $history): ?>
	<tr>
		<td><?php echo $history['History']['participant-phone']; ?>&nbsp;</td>
		    <td><?php echo ucfirst($history['History']['message-direction']); ?>&nbsp;</td>
		    <td><?php if (isset($history['History']['message-status'])) echo $history['History']['message-status']; ?>&nbsp;</td>
		    <td><?php if (isset($history['History']['failure-reason'])) echo $history['History']['failure-reason']; ?>&nbsp;</td>
		    <td><?php echo $history['History']['message-content']; ?>&nbsp;</td>
		    <td><?php echo $this->Time->format('d/m/Y H:i:s', $history['History']['timestamp']); ?>&nbsp;</td>
		</tr>
	<?php endforeach; ?>
	<?php } ?>
	</table>
	</div>
</div>	

<?php echo $this->Js->writeBuffer(); ?>
