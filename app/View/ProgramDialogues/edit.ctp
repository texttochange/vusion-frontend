<div class="index width-size">
	<?php 
    $contentTitle = null;
    if (isset($dialogue)) {
        $contentTitle = __('Edit Dialogue'); 
        if (!$dialogue['Dialogue']['activated'])  {
            $contentTitle = $contentTitle. " " . $this->Html->tag('span', __('(draft)', array('class'=>'ttc-dialogue-draft'))); 
        }
    } else {
        $contentTitle = __('Create Dialogue');
    }

    $contentActions = array();
    $contentActions[] = $this->Html->link( __('Cancel'), 
        array(
          'program' => $programDetails['url'],
          'controller' => 'programHome',
          'action' => 'index'),
        array('class' => 'ttc-button'));

    $contentActions[] = $this->Html->link( __('Save'),
        array(
          'program' => $programDetails['url'],
          'controller' => 'programHome',
          'action' => ''),
        array('class'=>'ttc-button dynamic-form-save'));
    $this->Js->get('.dynamic-form-save')->event('click', 'formSubmit();', true);

    if (isset($dialogue)) {
        if (!$dialogue['Dialogue']['activated']) {
          $contentActions[] = $this->Html->link(
            __('Activate'), 
            array(
              'program' => $programDetails['url'],
              'action' => 'activate', 
              'id' => $dialogue['Dialogue']['_id']), 
            array('class'=>'ttc-button'));
        }

        $contentActions[] = $this->Html->link(__('Test send all messages'), 
        array(
            'program'=>$programDetails['url'],
            'action'=>'testSendAllMessages', 
            'id'=>$dialogue['Dialogue']['_id']), 
        array('class'=>'ttc-button'));
    }
   
    echo $this->element('header_content', compact('contentTitle', 'contentActions'));

    ?>
    <div class="ttc-display-area display-height-size">
    <?php 
    echo $this->Html->tag('form', null, array('id'=> 'dynamic-generic-program-form')); 
    echo "</form>";
    echo "<br/>";
    echo $this->Html->tag('span', __('Save'), array('class'=>'ttc-button dynamic-form-save'));
   ?>
   <?php
   $this->Js->get("#dynamic-generic-program-form");
   if (isset($dialogue))
              $this->Js->each('$(this).buildTtcForm("Dialogue", '.$this->Js->object($dialogue['Dialogue']).', "javascript:saveFormOnServer()")', true);
   else
   $this->Js->each('$(this).buildTtcForm("Dialogue", null, "javascript:saveFormOnServer()")', true);
   ?>
	</div>	
	<?php
	$this->DynamicOptions->setOptions(
        $currentProgramData, $conditionalActionOptions, $contentVariableTableOptions, 
        $dialogue, $dynamicOptions);
	?>
</div>