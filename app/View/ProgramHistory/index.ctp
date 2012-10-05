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
		        $filterParams = 
		        $this->Js->get('#export-type')->event('change', '
	                window.location = "http://"+window.location.host+"/'.$url.'"+$("#export-type option:selected").val()+window.location.search;	                
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
	    $("#advanced_filter_form").show(hasNoStackFilter());
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
               $("#advanced_filter_form").show(hasNoStackFilter());
           ');
       }
   ?>
   <div id="advanced_filter_form" class="ttc-advanced-filter">
   <?php
       $this->Js->set('myOptions', $filterFieldOptions);
       $this->Js->set('typeConditionOptions', $filterTypeConditionsOptions);
       $this->Js->set('statusConditionOptions', $filterStatusConditionsOptions);
       $this->Js->set('dialogueConditionOptions', $filterDialogueConditionsOptions);
       
       $this->Js->get('$(\"#filter_field\"):focus')->event('change','
               var fieldOption = $("$(\"#filter_field\"):focus option:selected").val();
               if($(document.activeElement).attr("id") == "filter_field")
                   supplyConditionOptions(fieldOption);
           ');
       echo $this->Html->tag('span', 'Hide', array('id'=>'hideAdvFilter', 'class'=>'ttc-action-link', 'style'=>'float:right'));
       //echo "<h5>Filter Options</h5>";
       echo $this->Form->create('History', array('type'=>'get', 'url'=>array('program'=>$programUrl, 'action'=>'index')));
       if (isset($this->params['url']['filter_field'])) {
           $count = 1; // the stack filters and filter fields will always have names begining with index 1.
           foreach (array_keys($this->params['url']['filter_field']) as $key) {
               $this->Js->get('document')->event('ready','addStackFilter();
                   $("select[name=\'filter_field['.$count.']\']").focus();
                   $("select[name=\'filter_field['.$count.']\'] > option").each(function(){
                       if(this.value == "'.$this->params['url']['filter_field'][$key].'"){
                           $(this).attr("selected",true);
                           supplyConditionOptions($(this).val());
                       }
                   });
               ');
               $count++;
           }
       }
       if (isset($this->params['url']['filter_type'])) {
           $this->Js->get('document')->event('ready','
               $("select[name=\'filter_type\'] > option").each(function(){
                   if(this.value == "'.$this->params['url']['filter_type'].'"){
                       $(this).attr("selected",true);
                   }
               });
           ');
       }
       if (isset($this->params['url']['filter_status'])) {
           $this->Js->get('document')->event('ready','
               $("select[name=\'filter_status\'] > option").each(function(){
                   if(this.value == "'.$this->params['url']['filter_status'].'"){
                       $(this).attr("selected",true);
                   }
               });
           ');
       }
       if (isset($this->params['url']['filter_from'])) {
           $this->Js->get('document')->event('ready','
               $("input[name=\'filter_from\']").val("'.$this->params['url']['filter_from'].'");
           ');
       }
       if (isset($this->params['url']['filter_to'])) {
           $this->Js->get('document')->event('ready','
               $("input[name=\'filter_to\']").val("'.$this->params['url']['filter_to'].'");
           ');
       }
       if (isset($this->params['url']['filter_phone'])) {
           $this->Js->get('document')->event('ready','
               $("input[name=\'filter_phone\']").val("'.$this->params['url']['filter_phone'].'");
           ');
       }
       if (isset($this->params['url']['filter_content'])) {
           $this->Js->get('document')->event('ready','
               $("input[name=\'filter_content\']").val("'.$this->params['url']['filter_content'].'");
           ');
       }
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
			<th><?php echo $this->Paginator->sort('participant-phone', __('Phone'), array('url'=> array('program' => $programUrl, '?'=>$this->params['url'])));?></th>
			<th><?php echo $this->Paginator->sort('message-direction', __('Direction'), array('url'=> array('program' => $programUrl, '?'=>$this->params['url'])));?></th>
			<th><?php echo $this->Paginator->sort('message-status', __('Status'), array('url'=> array('program' => $programUrl, '?'=>$this->params['url'])));?></th>
			<th><?php echo $this->Paginator->sort('failure-reason', __('Failure Reason'), array('url'=> array('program' => $programUrl, '?'=>$this->params['url'])));?></th>
			<th><?php echo $this->Paginator->sort('message-content', __('Message'), array('url'=> array('program' => $programUrl, '?'=>$this->params['url'])));?></th>
			<th><?php echo $this->Paginator->sort('timestamp', __('Time'), array('url'=> array('program' => $programUrl, '?'=>$this->params['url'])));?></th>
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
		<td><?php echo h($status['History']['message-direction']); ?>&nbsp;</td>
		<td><?php echo h($status['History']['message-status']); ?>&nbsp;</td>
		<td><?php if (isset($status['History']['failure-reason'])) echo h($status['History']['failure-reason']); ?>&nbsp;</td>
		<td><?php echo h($status['History']['message-content']); ?>&nbsp;</td>
		<td><?php echo $this->Time->format('d/m/Y H:i:s', $status['History']['timestamp']); ?>&nbsp;</td>		
	</tr>
	<?php endforeach; ?>
	<?php } ?>
	</table>
	</div>

	<div class="paging">
	<?php
	    echo "<span class='ttc-page-count'>";
	    echo $this->Paginator->counter(array(
	        'format' => __('{:start} - {:end} of {:count}')
	    ));
	    echo "</span>";
		echo $this->Paginator->prev('< ' . __('previous'), array('url'=> array('program' => $programUrl, '?'=>$this->params['url'])), null, array('class' => 'prev disabled'));
		//echo $this->Paginator->numbers(array('separator' => '', 'url'=> array('program' => $programUrl, '?'=>$this->params['url'])));
		echo $this->Paginator->next(__('next') . ' >', array('url'=> array('program' => $programUrl, '?'=>$this->params['url'])), null, array('class' => 'next disabled'));
		//echo $this->Paginator->next(__('last') . ' >|', array('url'=> array('program' => $programUrl, '?'=>$this->params['url'])), null, array('class' => 'next disabled'));
	?>
    </div>
</div>	

<?php echo $this->Js->writeBuffer(); ?>
