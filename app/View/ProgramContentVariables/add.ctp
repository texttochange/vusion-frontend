<?php
    $this->RequireJs->scripts(array("jquery"));
?>
<div class="content_variables form width-size">
    <?php
        $contentTitle   = __('Add Content Variable'); 
        $contentActions = array();
        
        $contentActions[] = $this->Html->link( __('Cancel'), 
        array(
          'program' => $programDetails['url'],
          'action' => 'index'),
        array('class' => 'ttc-button'));
        
        $contentActions[] = $this->Html->link(__('Save'),
            array(),
            array('class'=>'ttc-button',
                'id' => 'button-save'));
        $this->Js->get('#button-save')->event('click',
            '$("#ContentVariableAddForm").submit()' , true);
		
		echo $this->element('header_content', compact('contentTitle', 'contentActions'));
    ?>
    <div class="ttc-display-area">
    <?php echo $this->Form->create('ContentVariable'); ?>
    <fieldset>
       <?php echo $this->Form->input('keys', array('label' => __('keys'))); ?>
       <?php echo $this->Form->input('value', array('label' => __('value'))); ?>
    </fieldset>
       <?php echo $this->Form->end(__('Save')); ?>
   </div>
</div>
