<div class="index width-size">
	<?php 
    $content_title = null;
    if (isset($dialogue)) {
        $content_title = __('Edit Dialogue'); 
        if (!$dialogue['Dialogue']['activated'])  {
            $content_title = $this->Html->tag('span', __('(draft)', array('class'=>'ttc-dialogue-draft'))); 
        }
    } else {
        $content_title = __('Create Dialogue');
    }

    $content_actions = array();
    $content_actions[] = $this->Html->link( __('Cancel'), 
        array(
          'program' => $programDetails['url'],
          'controller' => 'programHome',
          'action' => 'index'),
        array('class' => 'ttc-button'));

    $content_actions[] = $this->Html->tag('div', __('Save'), 
        array('class'=>'ttc-button dynamic-form-save'));
    $this->Js->get('.dynamic-form-save')->event('click', 'formSubmit();', true);

    if (isset($dialogue)) {
        $content_actions[] = $this->Html->link(__('Activate'), 
            array(
                'program' => $programDetails['url'],
                'action' => 'activate', 
                'id' => $dialogue['Dialogue']['_id']), 
        array('class'=>'ttc-button'));
    }

    $content_actions[] = $this->Html->link(__('Test send all messages'), 
        array(
            'program'=>$programDetails['url'],
            'action'=>'testSendAllMessages', 
            'id'=>$dialogue['Dialogue']['_id']), 
        array('class'=>'ttc-button'));

    echo $this->element('header_content', compact('content_title', 'content_actions'));

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
    $offsetConditionOptions = array(); //array('value'=> null, 'html' => __('Choose one question...'));
    if (isset($dialogue['Dialogue']['interactions'])) {
        foreach($dialogue['Dialogue']['interactions'] as $interaction) {
            if ($interaction['type-interaction']!='question-answer' and $interaction['type-interaction']!='question-answer-keyword')
                continue;
            $offsetConditionOptions[] = array(
                'value' => $interaction['interaction-id'],
                'html' => (isset($interaction['content']) ? $interaction['content'] : "")
                );
        }
    }
    $this->Js->set('offset-condition-interaction-idOptions', $offsetConditionOptions);
    
    $dialogueOptions = array();
    foreach($currentProgramData['dialogues'] as $dialogue) {
        if ($dialogue['Active']) {
            $dialogueOptions[] = array(
                'value' => $dialogue['Active']['dialogue-id'],
                'html' => $dialogue['Active']['name']
                );
        }
    }
    $this->Js->set('enrollOptions', $dialogueOptions);
    $this->Js->set('subcondition-fieldOptions', $conditionalActionOptions);
    $this->Js->set('dymanicOptions', $dynamicOptions);
    ?>
</div>