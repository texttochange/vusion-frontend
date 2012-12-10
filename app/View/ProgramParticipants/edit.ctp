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
	            $profileArray = array();
	            $profiles = $this->data['Participant']['profile'];	            
	            foreach ($profiles as $profile) {
	                $result = null;
	                foreach ($profile as $key => $value) {
	                    if ($value != null)
	                        $result.= $value.":";
	                    else
	                        $result=substr_replace($result, "", -1);
	                }
	                $profileArray[] = $result;
	            }
	            $profileData = implode(",", $profileArray);
	            echo $this->Form->input(__('profile'), array('rows'=>5, 'value'=>$profileData));
	            $tagsArray = explode(",",implode(",", $this->data['Participant']['tags']));
	            $tagsString = implode(", ",$tagsArray);
	            echo $this->Form->input(__('tags'), array('rows'=>5, 'value'=>$tagsString));
	        ?>
	    </fieldset>
	<?php echo $this->Form->end(__('Save'));?>
	</div>
</div>
