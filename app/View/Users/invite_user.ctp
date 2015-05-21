<?php
    echo $this->Html->tag('div', null, array('class'=>'ttc-login-container'));
    echo $this->Html->tag('h3', __('Invite User'));
    if (isset($validationErrors)) {
        $this->Form->validationErrors['User'] = $validationErrors['User'];
        $this->Form->validationErrors['Program'] = $validationErrors['Program'];
    }
    echo $this->Form->create('User', array('url' => array('controller' => 'users', 'action' =>'inviteUser')));
    echo $this->Form->input('email', array('label' => __('Email')));
    echo $this->Form->input('group_id', array('label' =>__('Group id')));
    $options = $programs;      
    echo $this->Form->input(
        'Program', array(
            'options'=>$options,
            'type'=>'select',
            'multiple'=>true,
            'label'=>__('Program'),                 
            'style'=>'margin-bottom:0px'
            ));
    $this->Js->get('document')->event('ready','$("#ProgramProgram").chosen();');
    echo $this->Form->checkbox('invite_disclaimer', array('class' => 'ttc-checkbox'));
	echo $this->Html->tag('label',__(' I agree that TTC will not be held accountable for misuse of this feature.'), array('class'=>'danger'));
    echo $this->Form->end(array('label' => __('Send'), 'id' => 'send-invite', 'onclick' => 'disableSend()'));   
    echo '<div id = "sending-email"></div>';
?>