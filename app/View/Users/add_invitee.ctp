<?php
    echo $this->Html->tag('div', null, array('class'=>'ttc-login-container'));
    echo $this->Html->tag('h3', __('Add User'));
    echo $this->Form->create('User', array('action' => 'addInvitee'));
	echo $this->Form->input('username', array('label' => __('Username')));
	echo $this->Form->input('password', array('label' => __('Password')));
	echo $this->Form->input('email', array('label' => __('Email')));
    echo $this->Form->end(__('Submit'));
?>

