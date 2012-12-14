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
	            $profiles = $this->data['Participant']['profile'];
	            if (is_array($profiles)) {
	                $profileArray = array();
	                foreach ($profiles as $profile) {
	                    $result = null;
	                    foreach ($profile as $key => $value) {
	                        if ($value != null)
	                            $result.= $value.":";
	                        else {
	                            if (strrpos($result,':') == strlen($result)-1)
	                                $result=substr_replace($result, "", -1);
	                        }
	                    }
	                    $profileArray[] = $result;
	                }
	                $profileData = implode(",", $profileArray);
	            } else {
	                $profileData = $profiles;
	            }
	            echo $this->Form->input(__('profile'), array('rows'=>5, 'value'=>$profileData));
	            $tags = $this->data['Participant']['tags'];
	            if (is_array($tags)) {
	                $tagsArray = explode(",",implode(",", $tags));
	                $tagsString = implode(", ",$tagsArray);
	            } else {
	                $tagsString = $tags;
	            }
	            echo $this->Form->input(__('tags'), array('rows'=>5, 'value'=>$tagsString));
	            $enrolled = $participant['Participant']['enrolled'];
	            $dialogueOptions = array();
	            $selected = array();
	            foreach ($dialogues as $key => $value) {
	                $dialogueOptions[$key] = $value['name'];
	                if (is_array($enrolled)) {
	                    foreach ($enrolled as $enrolledIn) {
	                        if ($key == $enrolledIn['dialogue-id']) {
	                            $selected[] = $key;
	                        }
	                    }
                    }
	            }
	            echo $this->Form->input('Participant.enrolled',
	                array('type' => 'select',
	                    'multiple' => true,
	                    'options' => $dialogueOptions,
	                    'selected' => $selected,
	                    'empty' => 'None'
	                    )
	                );
	        ?>
	    </fieldset>
	<?php echo $this->Form->end(__('Save'));?>
	</div>
</div>
