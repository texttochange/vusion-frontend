<?php
    $this->RequireJs->scripts(array("generic-program", "ttc-utils", "counter"));
?>
<div class="request form width-size">
    <?php
        $contentTitle   = __('Add Request'); 
        $contentActions = array();
        
        $contentActions[] = $this->Html->link( __('Cancel'), 
        array(
          'program' => $programDetails['url'],
          'controller' => 'programHome',
          'action' => 'index'),
        array('class' => 'ttc-button'));
        
        $contentActions[] = $this->Html->link(__('Save'),
            array(),
            array('class'=>'ttc-button dynamic-form-save'));
        $this->Js->get('.dynamic-form-save')->event('click',
		    'formSubmit()' , true); 
		
		echo $this->element('header_content', compact('contentTitle', 'contentActions'));
    ?>
    <div class="ttc-display-area display-height-size">
	    <?php 
	    echo $this->Html->tag('form', null, array(' id'=> 'dynamic-generic-program-form'));
	    echo "</form>";
	    $this->RequireJs->each('$("#dynamic-generic-program-form").buildTtcForm("Request", null, "javascript:saveFormOnServer()")', true);
        $this->DynamicOptions->setOptions(
            $currentProgramData, $conditionalActionOptions, $contentVariableTableOptions);
        $this->RequireJs->runLine('addCounter();');
	    ?>
	    <br/>
	    <?php  echo $this->Html->tag('span', __('Save'), array('class'=>'ttc-button dynamic-form-save')); ?>
	</div>
</div>
