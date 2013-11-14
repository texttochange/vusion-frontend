<h2><?php echo __('Reset Password')?></h2>
<?php
    
    echo $this->Form->create(
        'User', array('url'=> array(
            'controller' => 'users',
            'action' =>'resetPassword')));
    echo $this->Form->input(
        'text', array(
            'label' => 'Email',
            'id' => 'emailresetpassword',
            'name' => 'emailEnter'));
    echo $this->Html->image($this->Html->url(array('controller'=>'users', 'action'=>'captcha'), true), array('id'=>'imageCaptcha'));
    echo '<p> <a href="#" id="captchaReload"> can\'t read? Reload</a></p>';
    $this->Js->get('#captchaReload')->event('click', 'captchaReload();');
    echo '<p>Enter security code shown above:</p>';
    echo $this->Form->input('User.captcha', array('autocomplete'=>'off', 'label'=>false, 'id'=>'captchaField', 'name'=>'captchaField'));
    echo $this->Form->submit(__('Reset Password', true));
    echo $this->Form->end();
?>
