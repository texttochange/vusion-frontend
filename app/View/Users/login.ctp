<h3> <?php echo __('Login') ?></h3>
<?php
echo $this->Form->create('User', array('url' => array('controller' => 'users', 'action' =>'login')));
echo $this->Form->input('User.email');
echo $this->Form->input('User.password');
echo $this->Form->end(__('Login',true));
?>
