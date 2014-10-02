<?php
//echo "hello";
    echo $this->Html->tag('div', null, array('class'=>'ttc-login-container'));
    echo $this->Html->tag('h2', __('Invite User'));
    echo $this->Form->create('User', array('url' => array('controller' => 'users', 'action' =>'inviteUser')));
    echo $this->Form->input(
        'text', array(
            'label' => 'Email',
            'id' => 'emailofinvitee',
            'name' => 'emailInvitee'));
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
    echo $this->Form->checkbox('invite_disclaimer');
	echo $this->Html->tag('label',__(' I agree that TTC will not be held accountable for misuse of this feature.'), array('class'=>'danger'));
    echo $this->Form->end(__('Send', true));
?>