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
        <?php echo $this->Form->create('Participant');?>
        <fieldset>		
            <?php
                echo $this->Html->tag('div', __('Program Join Type '), array('style'=>'margin-bottom:0px'));
                $options = array(
                    'import' => __('Import'),
                    'optin-keyword' => __('Optin from Keyword'));
                $attributes = array(
                    'legend' => false,
                    'style' => 'margin-left:5px',
                    );
                echo "<div>";
                echo $this->Form->radio(
                    'join-type',
                    $options,
                    $attributes);
                echo '</div>';
            ?>
        </fieldset>
        <?php echo $this->Form->end(__('Save'));?>
	</div>
</div>

