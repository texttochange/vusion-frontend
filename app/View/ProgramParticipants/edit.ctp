<?php
	$this->RequireJs->scripts(array("chosen"));
?>
<div class="participants form width-size">
    <?php
        $contentTitle   = __('Edit Participant'); 
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
           '$("#ParticipantEditForm").submit()' , true);
		
       if (isset($participant['Participant']['simulate']) && ($participant['Participant']['simulate'])) {
            $contentActions[] = $this->AclLink->generateButton(
                __('Simulate'),
                $programDetails['url'],
                'programParticipants',
                'simulateMo',
                array('class'=>'ttc-button'),
                $participant['Participant']['_id']);
        }
       
		echo $this->element('header_content', compact('contentTitle', 'contentActions'));
    ?>
	<div class="ttc-display-area display-height-size">
	<?php echo $this->Form->create('Participant');?>
	    <fieldset>
	        <?php
	            echo $this->Form->input('phone', array('label' => __('Phone')));	            
	            $profiles = $this->data['Participant']['profile'];
	            if (is_array($profiles)) {
	                $profileArray = array();
	                foreach ($profiles as $profile) {
	                    $profileArray[] = $profile['label'].":".$profile['value'];
	                }
	                $profileData = implode(",", $profileArray);
	            } else {
	                $profileData = $profiles;
	            }
	            echo $this->Form->input('profile', array('label' => __('Profile'), 'rows'=>4, 'value'=>$profileData, 'required' => false));
	            $tags = $this->data['Participant']['tags'];
	            if (is_array($tags)) {
	                $tagsArray = explode(",",implode(",", $tags));
	                $tagsString = implode(", ",$tagsArray);
	            } else {
	                $tagsString = $tags;
	            }
	            echo $this->Form->input('tags', array('label' => __('Tags'), 'rows'=>4, 'value'=>$tagsString, 'required' => false));
	            $options = $selectOptions;
	            $selected = $oldEnrolls;
	            echo $this->Form->input('enrolled', array('options'=>$options,
	                'type'=>'select',
	                'multiple'=>true,
	                'label'=>__('Enrolled In'),
	                'selected'=>$selected,
                    'style'=>'margin-bottom:0px'
                    ));
	            $this->RequireJs->runLine('$("#ParticipantEnrolled").chosen();');
	        ?>
	    </fieldset>
	<?php echo $this->Form->end(__('Save'));?>
	</div>
</div>
