<?php
echo $this->Html->tag('div', null, array('class'=>'ttc-login-container'));
echo $this->Html->tag('h2', __('Report Issue'));
echo $this->Form->create('ReportIssue', array('url' => array('controller' => 'users', 'action' =>'reportIssue')));
echo $this->Form->input('text', array(
	'label' => __('Your Name'),
	'id' => 'yourName',
	'name' => 'yourName',
	'value' =>  $this->Session->read('Auth.User.username'),
	'readonly' => 'true'
	));
echo $this->Form->input('text', array(
	'label' => __('Your Email'),
	'id' => 'yourEmail', 
	'name' => 'yourEmail',
	'value' =>  $this->Session->read('Auth.User.email'),
	'readonly' => 'true'
	));
echo $this->Form->textarea('text', array('placeholder' => __('Message'), 'id' => 'reportIssueMessage', 'name' => 'reportIssueMessage', 'class' => 'report-message'));
echo $this->Form->end(__('Submit'));
?>
