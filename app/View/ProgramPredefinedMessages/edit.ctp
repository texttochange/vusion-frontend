<div class="predefined_messages form width-size">
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
        <?php $this->Js->get('#button-save')->event('click', '$("#PredefinedMessageEditForm").submit()' , true);?>
	</ul>
    <h3><?php echo __('Edit Predefined Message'); ?></h3>
    <div class="ttc-display-area">
    <?php echo $this->Form->create('PredefinedMessage'); ?>
    <fieldset>
       <?php echo $this->Form->input(__('name'), array('id' => 'name')); ?>
       <?php echo $this->Form->input(__('content'), array('rows'=>5)); ?>
       <?php $this->Js->get('document')->event('ready','addContentFormHelp();'); ?>
    </fieldset>
       <?php echo $this->Form->end(__('Save')); ?>
   </div>
</div>
