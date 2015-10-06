<?php
    $this->RequireJs->runLine('$("[class*=success]").delay(5000).fadeOut(1000);');
?>
<div class="table" style="width:100%">
	<div class="status-message row"> 
	<div class="flash-box" style='top:0px'>
    <?php
    echo $this->Html->tag('div', '', array(
        'id' => 'connectionState',
        'class' => 'connection-message',
        'style' => 'display: none'));
    ?>
    </div>
    <div class="flash-box" style='top:30px'>
    <?php 
    echo $this->Session->flash(); 
    if (!$this->Session->flash()) {
        echo $this->Html->tag('div', '', array(
            'id' => 'flashMessage', 
            'class' => 'message', 
            'style' => 'display: none'));
    }
    ?>
    </div>
    <div class="flash-box" style='top:63px'>
    <?php
    ## Flash message for the credit manager's status
    if (isset($creditStatus)) {
        echo $this->CreditManager->flash(
            $creditStatus,
            (isset($programDetails['settings']) ? $programDetails['settings'] : null));
    }
    ?>
    </div>
	</div>
	<div class="row">
	<div class="ttc-left-header cell">
		<?php 
			echo $this->Html->image('vusion-logo-wide.png', array(
				'url' => array('controller'=> 'programs', 'action'=>'index')
				));
		?> 
	</div>			
	<div class="ttc-right-header cell"> 
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
	<div class="ttc-central-header cell">
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
</div>
</div>