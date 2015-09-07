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
        <?php echo $this->Form->create('Participant', array('type' => 'file'));?>
        <fieldset>		
            <?php
                echo $this->Html->tag('div', __('Program Join Type '), array('style'=>'margin-bottom:0px'));
                $options = array(
                    'import' => __('Import'),
                    'optin-keyword' => __('Optin from Keyword'));
                $attributes = array(
                    'legend' => false,
                    'style' => 'margin-left:5px',
                    'id' => 'join-type');
                echo "<div class='simulator-add-participant'>";
                echo $this->Form->radio(
                    'join-type',
                    $options,
                    $attributes);
                
                echo $this->Form->input(
                    'message',
                    array(
                        'disabled' => true,
                        'rows' =>2,
                        'label' => __('Message'),
                        'name'=>'message',
                        'id' => 'smessage'));
                echo '</div>';
                               
                $this->Js->get("input[name*='join-type']")->event('change','
                    if($(this).val() == "optin-keyword") {
                    $("#smessage").attr("disabled", false);
                    } else {
                    $("#smessage").attr("disabled", "disabled");
                    $("#smessage").val("");
                    }');
                
            ?>
        </fieldset>
        <?php echo $this->Form->end(__('Save'));?>
	</div>
</div>

