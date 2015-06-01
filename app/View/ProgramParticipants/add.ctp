<div class="participants form width-size">
    <?php
        $contentTitle   = __('Add Participant'); 
        $contentActions = array();
        $controller     = 'programParticipants';
        
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
        
        $contentActions[] = $this->Html->link(__('Import Participant(s)'),
		    array('program' => $programDetails['url'],
		        'controller' => $controller,
		        'action' => 'import'), 
		    array('class'=>'ttc-button'));
		
		$contentActions[] = $this->Html->link(__('View Participant(s)'),
		    array('program' => $programDetails['url'],
		        'controller' => $controller,
		        'action' => 'index'),
		    array('class'=>'ttc-button'));
		
		echo $this->element('header_content', compact('contentTitle', 'contentActions', 'controller'));
    ?>
	<div class="ttc-display-area display-height-size">
	    <?php echo $this->Form->create('Participant');?>
	        <fieldset>		
	            <?php
	                echo $this->Form->input('phone', array('label' => __('Phone')));
	            ?>
	        </fieldset>
	    <?php echo $this->Form->end(__('Save'));?>
	</div>
</div>

