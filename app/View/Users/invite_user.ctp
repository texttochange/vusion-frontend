<?php
    echo $this->Html->tag('div', null, array('class'=>'ttc-login-container'));
    echo $this->Html->tag('h2', __('Invite User'));
    echo $this->Form->create('User', array('url' => array('controller' => 'users', 'action' =>'inviteUser')));
?>
<fieldset>
<?php
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
    echo $this->Form->checkbox('invite_disclaimer');
	echo $this->Html->tag('label',__(' I agree that TTC will not be held accountable for misuse of this feature.'), array('class'=>'danger'));
?>
</fieldset>
<?php
    echo $this->Form->end(__('Send', true));
?>