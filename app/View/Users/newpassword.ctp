<?php
    echo $this->Html->tag('div', null, array('class'=>'ttc-login-container'));
    echo $this->Html->tag('h2', __('New Password'));
    echo $this->Form->create('User', array('url' => array('controller' => 'users', 'action' =>'newpassword', $userId)));
    echo $this->Form->input('password', array('label' => __('New Password'), 'id' => 'newPassword', 'name' => 'newPassword'));
    echo $this->Form->input('password', array('label' => __('Confirm Password'), 'id' => 'confirmPassword', 'name' => 'confirmPassword'));
    echo $this->Form->end(__('Submit'));
?>

