<h2><?php echo __('New Password')?></h2>
<?php
    echo $this->Form->create('User', array('url' => array('controller' => 'users', 'action' =>'newpassword', $userId)));
    echo $this->Form->input('password', array('label' => __('New Password'), 'id' => 'newPassword', 'name' => 'newPassword'));
    echo $this->Form->input('password', array('label' => __('Confirm Password'), 'id' => 'confirmPassword', 'name' => 'confirmPassword'));
    echo $this->Form->end(__('Submit'));
?>

