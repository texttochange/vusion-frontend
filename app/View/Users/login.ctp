<?php 
echo $this->Html->tag('div', null, array('class'=>'ttc-login-container'));
echo $this->Html->tag('h3', __('Login'), array('class' => 'ttc-login-title')); 
echo $this->Form->create('User', array('url' => array('controller' => 'users', 'action' =>'login')));
echo $this->Form->input('User.email', array('class'=>'ttc-login-input'));
echo $this->Form->input('User.password', array('class'=>'ttc-login-input'));
echo $this->Form->end(__('Login',true));
?>
<div class="forget-password">
    <?php 
        echo $this->Html->link(__('forgot your password'),
            array('action' => 'requestPasswordReset'));
    ?>
</div>
