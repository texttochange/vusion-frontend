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
	   $this->Js->get('#advFilter')->event('click',
	       '$(".ttc-filter").hide();
	       $("#advanced_filter_form").show(hasNoStackFilter());');
	   echo "&nbsp;&nbsp;&nbsp;&nbsp;";
	   $options = array(); 
	   $options['non-matching-answers'] = __("Non matching answers");
	   if (isset($this->params['url']['filter_param'])) {
	       if (count($this->params['url']['filter_param'])==1 and isset($options[$this->params['url']['filter_param'][1][1]])) {
	           echo $this->Form->select('filter', $options, array('id'=> 'quick-filter', 'default' => $this->params['url']['filter_param'][1][1],'empty' => 'Quick Filter...'));
	       } else {
	           $this->Js->get('document')->event('ready','
	               $(".ttc-filter").hide();
	               $("#advanced_filter_form").show(hasNoStackFilter());
	               ');
	           $count = 1;
               foreach ($this->params['url']['filter_param'] as $filter) {
                   $thirdDrop = (isset($filter[3]) ? '$("select[name=\'filter_param['.$count.'][3]\']").val("'.$filter[3].'").children("option[value='.$filter[3].']").click();' : '');
                   $this->Js->get('document')->event('ready',
                       'addStackFilter();
                       $("select[name=\'filter_param['.$count.'][1]\']").val("'.$filter[1].'").children("option[value=\''.$filter[1].'\']").click();
                       if ($("input[name=\'filter_param['.$count.'][2]\']").length > 0) {
                            $("input[name=\'filter_param['.$count.'][2]\']").val("'.(isset($filter[2])? $filter[2]:'').'");
                       } else {
                           $("select[name=\'filter_param['.$count.'][2]\']").val("'.(isset($filter[2])? $filter[2]:'').'").children("option[value='.(isset($filter[2])? $filter[2]:'').']").click();
                           '. $thirdDrop .'
                       }',
                       true);
                   $count++;
               }	           
	       }   
	   } else { 
	       	echo $this->Form->select('filter', $options, array('id'=> 'quick-filter', 'empty' => 'Quick Filter...'));
	   }
	   $this->Js->get('#quick-filter')->event('change', '
	     if ($(this).val())
	         window.location.search = "?filter_param[1][1]="+$(this).val();
         else
             window.location.search = "?";');
	   echo $this->Form->end(); ?>
   </div>
   <div id="advanced_filter_form" class="ttc-advanced-filter">
   <?php
       $this->Js->set('myOptions', $filterFieldOptions);
       $this->Js->set('typeConditionOptions', $filterTypeConditionsOptions);
       $this->Js->set('statusConditionOptions', $filterStatusConditionsOptions);
       $this->Js->set('dialogueConditionOptions', $filterDialogueConditionsOptions);
       
       //Add the behavior to all filterstack 
       $this->Js->get(':regex(name,^filter_param\\\[\\\d+\\\]\\\[1\\\])')->event('change','supplyConditionOptions(this);', true);
       //Create the strack that have been previsously send
       echo $this->Html->tag('span', 'Hide', array('id'=>'hideAdvFilter', 'class'=>'ttc-action-link', 'style'=>'float:right'));
       echo $this->Form->create('History', array('type'=>'get', 'url'=>array('program'=>$programUrl, 'action'=>'index')));
     
       echo $this->Form->end(array("label" => "Filter"));       
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
