<div class="content_variables form width-size">
    <div class="ttc-page-title">
        <h3><?php echo __('Add Content Variable') ?></h3>
        <span class="tabs">
            <ul>
            <li class="selected">
                <a href="<?php echo $this->Html->url(array('program' => $programDetails['url'], 'action' => 'index')) ?>" >
                    <label><?php echo __("Keys/Value") ?></label>
                </a>
            </li>
            <li>
                <a href="<?php echo $this->Html->url(array('program' => $programDetails['url'], 'action' => 'addTable')) ?>" >
                    <label><?php echo __("Table") ?></label>
                </a>
            </li>
            </ul>
        </span>
        <ul class="ttc-actions">
            <li>
            <?php echo $this->Html->tag('span', __('Save'), array('class'=>'ttc-button', 'id' => 'button-save')); ?>
            <span class="actions">
            <?php
            echo $this->Html->link( __('Cancel'), 
                array(
                    'program' => $programDetails['url'],
                    'action' => 'index'	           
                    ));
            ?>
            </span>
            </li>
            <?php $this->Js->get('#button-save')->event('click', '$("#ContentVariableAddForm").submit()' , true);?>
        </ul>
    </div>
    <div class="ttc-display-area">
    <?php echo $this->Form->create('ContentVariable'); ?>
    <fieldset>
       <?php echo $this->Form->input('keys', array('label' => __('keys'))); ?>
       <?php echo $this->Form->input('value', array('label' => __('value'))); ?>
    </fieldset>
       <?php echo $this->Form->end(__('Save')); ?>
   </div>
</div>
