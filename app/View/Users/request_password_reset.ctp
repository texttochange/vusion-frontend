<?php
    echo $this->Html->tag('div', null, array('class'=>'ttc-login-container'));
    echo $this->Html->tag('h2', __('Request Password Reset'));
    echo $this->Form->create(
        'User', array('url'=> array(
            'controller' => 'users',
            'action' =>'requestPasswordReset')));
    echo $this->Form->input(
        'text', array(
            'label' => 'Email',
            'id' => 'emailresetpassword',
            'name' => 'emailEnter'));
    echo '<div class="input text">';
    echo $this->Html->image($this->Html->url(
        array('controller'=>'users', 'action'=>'captcha'), true),
        array('id'=>'imageCaptcha')).' <a href="#" id="captchaReload" class="captcha-reload">  can\'t read, get another word</a>';
    $this->Js->get('document')->event('ready', '
        var captchaSource = $("#imageCaptcha").attr("src");
        window.captchaSource = captchaSource;
        ');
    $this->Js->get('#captchaReload')->event(
        'click',
        '$("#captchaReload").click(function () {
        var captcha = $("#imageCaptcha");
        var source = window.captchaSource;
        captcha.attr("src", source+"?"+Math.random());
        return false;
        });');
    echo '</div>';
    echo $this->Form->input('User.captcha', array('autocomplete'=>'off', 'label'=>'Enter security code shown above:', 'id'=>'captchaField', 'name'=>'captchaField'));
    echo $this->Form->submit(__('Send', true));
    echo $this->Form->end();
?>
