<div class="participants form width-size">
    <?php
        $contentTitle       = __('Add Simulator Participant'); 
        $contentActions     = array();
        $addParticipantSpan = 'simulate';
        $controller         = 'programParticipants';
        
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
            '$("#ParticipantAddForm").submit()' , true);
        
        echo $this->element('header_content', compact('contentTitle', 'contentActions', 'controller', 'addParticipantSpan'));
    ?>
	<div class="ttc-display-area display-height-size">
       <?php echo $this->element('participant_add_tabs', array('type' => 'simulate')); ?>
	</div>
</div>

