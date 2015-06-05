<div class="ttc-left-header">
	<?php 
		echo $this->Html->image('vusion-logo-wide.png', array(
			'url' => array('controller'=> 'programs', 'action'=>'index')
			));
	?> 
</div>			
<div class="ttc-right-header"> 
	<?php
	if ($this->Session->read('Auth.User.id')) {
		echo $this->Html->link(
			__('Logout'),
			array('controller'=> 'users', 'action'=>'logout'),
			array('class' => 'ttc-link-header'));
		
		echo $this->Html->link(
			__('Hi %s', $this->Session->read('Auth.User.username')),
			array('controller'=> 'users', 'action'=>'view', $this->Session->read('Auth.User.id')),
			array('class' => 'ttc-link-header'));
	}
	?> 
</div>
<div class="ttc-central-header">
<?php
	if ($this->Session->read('Auth.User.id')) {
		$reportIssueUrl = $this->Html->url(array('controller' => 'users', 'action' => 'reportIssue'));			    
		echo $this->Html->link(
			__('Report Issue'),
			array(), 
			array('class' => 'ttc-link-header', 'url' => $reportIssueUrl, 'onclick'=> 'popupBrowser(this)'));
		
		echo $this->Documentation->link(); 
	}
	echo $this->AclLink->generateButton(
		__('Credit Viewer'), null, 'creditViewer', null, array('class'=>'ttc-link-header'));
	echo $this->AclLink->generateButton(
		__('Admin'), null, 'admin', null, array('class'=>'ttc-link-header'));
?>
</div>