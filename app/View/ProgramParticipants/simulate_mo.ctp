<div class="participant view width-size">
    <?php
        $contentTitle   = __('Simulate Participant'); 
        $contentActions[] = $this->AclLink->generateButton(
            __('Edit Participant'),
            $programDetails['url'],
            'programParticipants',
            'edit',
            array('class'=>'ttc-button'),
            $participant['Participant']['_id']);
        
        $contentActions[] = $this->AclLink->generateButton(
            __('View Participant'),
            $programDetails['url'],
            'programParticipants',
            'view',
            array('class'=>'ttc-button'),
            $participant['Participant']['_id']);
        
        echo $this->element('header_content', compact('contentTitle', 'contentActions'));
    ?>
    <div>
        <div class="simulator">
            <div>
            <div class="simulator-message">
                <div class = "ttc-simulator-output" >
                    <?php
                    echo $this->Html->tag('div', '<img src="/img/ajax-loader.gif" class="simulator-image-load">', array(
                        'class'=>'ttc-simulator-meesage',
                        'id' => 'simulator-output'));
                    ?>
                </div>
                <div>
                    <?php
                    echo $this->Form->create(null, array('id'=>'simulator-input'));
                    echo $this->Form->input(
                        'from',
                        array(
                            'value' => $participant['Participant']['phone'],
                            'name'=>'phone',
                            'type' => 'hidden'));
                    echo $this->Form->input(
                        'message', 
                        array(
                            'rows'=>3,
                            'label' => __('Message'),
                            'name' => 'message',
                            'id' => 'smessage',
                            'autofocus'));
                    echo $this->Form->end(array('label' => __('Send'), 'id'=>'send-button'));
                    
                    $this->Js->get('#send-button')->event(
                        'click',
                        $this->Js->request(
                            array('program'=>$programDetails['url'], 'action'=>'simulateMo.json'),
                            array('method' => 'POST',
                                'async' => true, 
                                'dataExpression' => true,
                                'data' => '$("#simulator-input").serialize()',
                                'success' => 'logMessageSent(event)'
                                )));
                    
                    $this->Js->get('#smessage')->event(
                        'keyup',
                        'logMessageSent(event)'
                        );
                    
                    $this->Js->get('document')->event(
                     'ready',
                     '$("#simulator-output").simulator({"phone": "'.$participant['Participant']['phone'].'"});');

                    ?>
                </div>
            </div>
            <div class="simulator-profile">
                <div class="simulator-profile-div" id = "simulator-profile">
                    <?php
                    echo '<img src="/img/ajax-loader.gif">';
                    ?>
                </div>
            </div> 
            <div>
        </div>
    </div>
</div>
<?php echo $this->Js->writeBuffer(); ?> 
