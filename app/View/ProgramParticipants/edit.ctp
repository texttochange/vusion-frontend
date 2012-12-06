<div class="participants form">
    <ul class="ttc-actions">		
        <li><?php echo $this->Html->tag('div', __('Save'), array('class'=>'ttc-button', 'id' => 'button-save')); ?></li>
        <?php $this->Js->get('#button-save')->event('click', '$("#ParticipantEditForm").submit()' , true);?>
	</ul>
	<h3><?php echo __('Edit Participant'); ?></h3>
	<div class="ttc-display-area">
	<?php echo $this->Form->create('Participant');?>
	    <fieldset>
	        <?php
	            echo $this->Form->input('phone');
	            echo $this->Form->input(__('Profile'), array('rows'=>5));
	            $retrieved_tags = implode(",", $this->data['Participant']['tags']);
	            echo $this->Form->input(__('tags'), array('rows'=>5, 'value'=>$retrieved_tags));
	            
	        ?>
	    </fieldset>
	<?php echo $this->Form->end(__('Save'));?>
	</div>
</div>
