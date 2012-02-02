<h2> <?php echo __('Login') ?></h2>
<?php
echo $this->Session->flash('auth');
echo $this->Form->create('User', array('url' => array('controller' => 'users', 'action' =>'login')));
echo $this->Form->input('User.username');
echo $this->Form->input('User.password');
echo $this->Form->end(__('Login',true));
?>
