<div class="status index">    
    <ul class="ttc-actions">
		<li>
		    <?php
		        echo $this->Form->create(null);
		        $exportOptions = array();
		        $exportOptions['export.csv'] = 'Export CSV'; 
		        $exportOptions['index.csv'] = 'Export Raw CSV';
		        $exportOptions['index.json'] = 'Export Json';
		        echo $this->Form->select('export',$exportOptions, array('id'=>'export-type', 'empty' => 'Export History...'));
		        echo $this->Form->end();
		        $url = $programUrl.'/programHistory/';
		        $this->Js->get('#export-type')->event('change', '
	                window.location = "http://"+window.location.host+"/'.$url.'"+$("#export-type option:selected").val();
	            ');
		    ?>
		</li>
	</ul>
    <h3><?php echo __('Program History'); ?></h3>	
   <div class="ttc-filter">
        <?php
	   echo $this->Form->create(null);
	   echo $this->Html->tag('span', 'Advanced Filter', array('id'=>'advFilter', 'class'=>'ttc-action-link'));
	   $this->Js->get('#advFilter')->event(
	    'click','
	    $(".ttc-filter").hide();
	    $("#advanced_filter_form").show();
	    ');
	   echo "&nbsp;&nbsp;&nbsp;&nbsp;";
	   $options = array(); 
	   $options['non_matching_answers'] = "Non matching answers";
	   if (isset($this->params['url']['filter']))
	        echo $this->Form->select('filter', $options, array('id'=> 'filter', 'default' => $this->params['url']['filter'],'empty' => 'Quick Filter...'));
	   else 
	       	echo $this->Form->select('filter', $options, array('id'=> 'filter', 'empty' => 'Quick Filter...'));
	   $this->Js->get('#filter')->event('change', '
	     if ($("#filter option:selected").val())
	         window.location.search = "?filter="+$("#filter option:selected").val();
         else
             window.location.search = "?";
	   ');
	   $this->Js->get('document')->event('ready','
           $("#filter_from").datepicker();
           $("#filter_to").datepicker();
       ');
	   echo $this->Form->end(); ?>
   </div>
   <?php
       if (preg_grep('/^filter_/', array_keys($this->params['url']))) {
           $this->Js->get('document')->event('ready','
               $(".ttc-filter").hide();
               $("#advanced_filter_form").show();
           ');
       }
   ?>
   <div id="advanced_filter_form" class="ttc-advanced-filter">
   <?php
       echo "<h5>Filter Options</h5>";
       echo $this->Form->create('History', array('type'=>'get', 'url'=>array('program'=>$programUrl, 'action'=>'index')));
       echo $this->Html->tag('span', 'Hide', array('id'=>'hideAdvFilter', 'class'=>'ttc-action-link', 'style'=>'float:right'));
       echo $this->Html->tag('label','Message Type');
       echo "&nbsp;";
       $optionsType = array();
       $optionsType['received'] = "received";
       $optionsType['sent'] = "sent";
       if (isset($this->params['url']['filter_type']))
	        echo $this->Form->select('filter_type', $optionsType, array('id'=> 'filter_type', 'default' => $this->params['url']['filter_type'], 'empty' => '....'));
	   else
            echo $this->Form->select('filter_type', $optionsType, array('id'=> 'filter_type', 'empty' => '.......'));
       echo "&nbsp;&nbsp;&nbsp;&nbsp;";
       echo $this->Html->tag('label','Message Status');
       echo "&nbsp;";
       $optionsStatus = array();
       $optionsStatus['pending'] = "pending";
       $optionsStatus['delivered'] = "delivered";
       $optionsStatus['failed'] = "failed";
       if (isset($this->params['url']['filter_status']))
	        echo $this->Form->select('filter_status', $optionsStatus, array('id'=> 'filter_status', 'default' => $this->params['url']['filter_status'],'empty' => '....'));
	   else
            echo $this->Form->select('filter_status', $optionsStatus, array('id'=> 'filter_status', 'empty' => '.......'));
       
            
        if (isset($this->params['url']['filter_from']))
            $dateFrom = $this->params['url']['filter_from'];
        else
            $dateFrom = null;
       echo $this->Form->input('filter_from', array('label'=>'from','id'=>'filter_from', 'value'=> $dateFrom));
       if (isset($this->params['url']['filter_to']))
            $dateTo = $this->params['url']['filter_to'];
        else
            $dateTo = null;
       echo $this->Form->input('filter_to', array('label'=>'to','id'=>'filter_to', 'value'=> $dateTo));
       if (isset($this->params['url']['filter_phone']))
            $phone = $this->params['url']['filter_phone'];
        else
            $phone = null;
       echo $this->Form->input('filter_phone', array('label'=>'phone','id'=>'filter_phone', 'value'=>$phone));
       echo $this->Form->end(__('Filter'));       
       $this->Js->get('#advanced_filter_form')->event('submit','
           $(":input[value=\"\"]").attr("disabled", true);
           return true;
           ');
       $this->Js->get('#hideAdvFilter')->event(
	    'click','
	    $(".ttc-filter").show();
	    $("#advanced_filter_form").hide();
	    ');
   ?>
   </div>
        
    <div class="ttc-display-area">    
	<table cellpadding="0" cellspacing="0">
	<tr>                                                                        
			<th><?php echo $this->Paginator->sort('phone', null, array('url'=> array('program' => $programUrl)));?></th>
			<th><?php echo $this->Paginator->sort('type', null, array('url'=> array('program' => $programUrl)));?></th>
			<th><?php echo $this->Paginator->sort('status', null, array('url'=> array('program' => $programUrl)));?></th>
			<th><?php echo $this->Paginator->sort('failure reason', null, array('url'=> array('program' => $programUrl)));?></th>
			<th><?php echo $this->Paginator->sort('message', null, array('url'=> array('program' => $programUrl)));?></th>
			<th><?php echo $this->Paginator->sort('time', null, array('url'=> array('program' => $programUrl)));?></th>
	</tr>
	<?php if (preg_grep('/^filter/', array_keys($this->params['url'])) && $statuses == null) { ?>
	    <tr>
	        <td colspan=6>No results found</td>
	    </tr>
	<?php } else {?>    
	<?php
	foreach ($statuses as $status): ?>
	<tr>
		<td><?php echo h($status['History']['participant-phone']); ?>&nbsp;</td>
		<td><?php echo h($status['History']['message-type']); ?>&nbsp;</td>
		<td><?php echo h($status['History']['message-status']); ?>&nbsp;</td>
		<td><?php if (isset($status['History']['failure-reason'])) echo h($status['History']['failure-reason']); ?>&nbsp;</td>
		<td><?php echo h($status['History']['message-content']); ?>&nbsp;</td>
		<td><?php echo $this->Time->format('d/m/Y H:i:s', $status['History']['timestamp']); ?>&nbsp;</td>		
	</tr>
	<?php endforeach; ?>
	<?php } ?>
	</table>
	</div>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>
	</p>

	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array('url'=> array('program' => $programUrl, '?'=>$this->params['url'])), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => '', 'url'=> array('program' => $programUrl, '?'=>$this->params['url'])));
		echo $this->Paginator->next(__('next') . ' >', array('url'=> array('program' => $programUrl, '?'=>$this->params['url'])), null, array('class' => 'next disabled'));
	?>
    </div>
</div>	

<?php echo $this->Js->writeBuffer(); ?>
