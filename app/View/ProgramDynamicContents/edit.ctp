<div class="dynamic_contents form width-size">
    <ul class="ttc-actions">
        <li>
        <?php echo $this->Html->tag('span', __('Save'), array('class'=>'ttc-button', 'id' => 'button-save')); ?>
        <span class="actions">
        <?php
        echo $this->Html->link( __('Cancel'), 
            array(
                'program' => $programDetails['url'],
                'controller' => 'programHome',
                'action' => 'index'	           
                ));
        ?>
        </span>
        </li>
        <?php $this->Js->get('#button-save')->event('click', '$("#DynamicContentEditForm").submit()' , true);?>
	</ul>
    <h3><?php echo __('Edit Dynamic Content'); ?></h3>
    <div class="ttc-display-area">
    <?php echo $this->Form->create('DynamicContent'); ?>
    <fieldset>
       <?php echo $this->Form->input(__('key')); ?>
       <?php echo $this->Form->input(__('value')); ?>
    </fieldset>
       <?php echo $this->Form->end(__('Save')); ?>
   </div>
</div>
