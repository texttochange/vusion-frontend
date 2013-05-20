<div class="participants form width-size">
    <ul class="ttc-actions">
        <li>
        <?php echo $this->Html->tag('span', __('Save'), array('class'=>'ttc-button', 'id' => 'button-save')); ?>
        <span class="actions">
        <?php
        echo $this->Html->link( __('Cancel'), 
            array(
                'program' => $programUrl,
                'controller' => 'programHome',
                'action' => 'index'	           
                ));
        ?>
        </span>
        </li>
        <?php $this->Js->get('#button-save')->event('click', '$("#ParticipantAddForm").submit()' , true);?>
		<li><?php echo $this->Html->link(__('Import Participant(s)'), array('program' => $programUrl, 'controller' => 'programParticipants', 'action' => 'import'), array('class'=>'ttc-button')); ?></li>
		<li><?php echo $this->Html->link(__('View Participant(s)'), array('program' => $programUrl, 'controller' => 'programParticipants', 'action' => 'index'), array('class'=>'ttc-button'));?></li>
	</ul>
	<h3><?php echo __('Add Participant'); ?></h3>
	<div class="ttc-display-area">
	    <?php echo $this->Form->create('Participant');?>
	        <fieldset>		
	            <?php
	                echo $this->Form->input('phone', array('label' => __('Phone')));
	            ?>
	        </fieldset>
	    <?php echo $this->Form->end(__('Save'));?>
	</div>
</div>

