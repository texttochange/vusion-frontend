<?php
    echo $this->Html->tag('div', null, array('class'=>'ttc-login-container'));
    echo $this->Html->tag('h2', __('Reset Password'));
    echo $this->Form->create(
        'User', array('url'=> array(
            'controller' => 'users',
            'action' =>'resetPassword')));
    echo $this->Form->input(
        'text', array(
            'label' => 'Email',
            'id' => 'emailresetpassword',
            'name' => 'emailEnter'));
    echo '<div class="input text">';
    echo $this->Html->image($this->Html->url(
        array('controller'=>'users', 'action'=>'captcha'), true),
        array('id'=>'imageCaptcha')).' <a href="#" id="captchaReload" class>  can\'t read, get another word</a>';
    $this->Js->get('#captchaReload')->event('click', 'captchaReload();');
    echo '</div>';
    echo $this->Form->input('User.captcha', array('autocomplete'=>'off', 'label'=>'Enter security code shown above:', 'id'=>'captchaField', 'name'=>'captchaField'));
    echo $this->Form->submit(__('Send', true));
    echo $this->Form->end();
?>
