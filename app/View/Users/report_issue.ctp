<div class='ttc-report-issue-container'>
	<h2><?php echo __('Report Issue'); ?></h2>
	<p><?php echo __('When reporting an issue add as much details as possible so that the tech team can <b>reproduce the issue</b>. Also keep in mind that a uploading a good screenshot is worth a thousand words.'); ?></p> 
    <?php
	echo $this->Form->create('ReportIssue', array('error'=> true, 'type' => 'file', 'url' => array('controller' => 'users', 'action' =>'reportIssue')));
	echo "<div class='input text' style='margin-bottom:0px'>";
	echo $this->Form->input(
		'reportIssueSubject', 
		array(
			'title' => __('Expected and current behavior'),
			'label' => __('Describe the issue'),
			'placeholder' => __('Expected and current behavior'),
			'class' => 'report-issue-describtion',
			'div' => false));
	if ($this->Form->isFieldError('reportIssueSubject')) {
	    echo $this->Form->error('reportIssueSubject');
	}
	echo "</div>";
	echo $this->Html->tag('label', __('How to reproduce the issue'), array('style'=>'padding-left:8px'));
	echo $this->Form->textarea(
		'reportIssueMessage', 
		array(
			'title' => __('How to reproduce step by step, copy/past the program url and occurence'),
			'placeholder' => __('How to reproduce step by step, copy/past the program url and occurence'),
			'class' => 'report-issue-message'));
	if ($this->Form->isFieldError('reportIssueMessage')) {
	    echo $this->Form->error('reportIssueMessage');
	}
	echo $this->Form->input('ReportIssue.Screenshort', array('type' => 'file'));	
	echo $this->Form->button(__('Report'), array('class'=>'ttc-button', 'type' => 'submit'));
	echo $this->Form->button(__('Close'), array('class'=>'ttc-button', 'onclick'=> 'popupBrowserClose()'));	
	?>	
</div>