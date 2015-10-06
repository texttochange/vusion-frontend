<?php
    $this->RequireJs->scripts(array("ttc-utils"));
?>
<div class="predefined_messages form width-size">
   <?php
        $contentTitle   = __('Add Predefined Message'); 
        $contentActions = array();
        
        $contentActions[] = $this->Html->link( __('Cancel'), 
        array(
          'program' => $programDetails['url'],
          'controller' => 'programHome',
          'action' => 'index'),
        array('class' => 'ttc-button'));
        
        $contentActions[] = $this->Html->link(__('Save'),
            array(),
            array('class'=>'ttc-button',
                'id' => 'button-save'));
        $this->Js->get('#button-save')->event('click',
            '$("#PredefinedMessageAddForm").submit()' , true);
		
		echo $this->element('header_content', compact('contentTitle', 'contentActions'));
    ?>
    <div class="ttc-display-area">
    <?php echo $this->Form->create('PredefinedMessage'); ?>
    <fieldset>
       <?php echo $this->Form->input('name', array('label' => __('name'), 'id' => 'name')); ?>
       <?php echo $this->Form->input('content', array('label' => __('content'), 'rows'=>5)); ?>
       <?php $this->RequireJs->runLine('
           addContentFormHelp();
           addCounter();
           '); ?>
    </fieldset>
       <?php echo $this->Form->end(__('Save')); ?>
   </div>
</div>
