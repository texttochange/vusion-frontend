<div class='ttc-report-issue-container'>
	<h2><?php echo __('Report Issue'); ?></h2>
	<p><?php echo __('When reporting an issue add as much details as possible so that the tech team can <b>reproduce the issue</b>. Also keep in mind that a uploading a good screenshot is worth a thousand words.'); ?></p> 
    <?php
    //Hack because ReportIssue is not a model
    if (isset($validationErrors)) {
    	$this->Form->validationErrors['ReportIssue'] = $validationErrors;
    }
	echo $this->Form->create('ReportIssue', array('error'=> true, 'type' => 'file', 'url' => array('controller' => 'users', 'action' =>'reportIssue')));
	echo $this->Form->input(
		'subject',
		array(
			'title' => __('Expected and current behavior'),
			'label' => __('Describe the issue'),
			'placeholder' => __('Expected vs current behavior'),
			'class' => 'report-issue-describtion',
			'div' => true));
	echo $this->Form->input(
		'message', 
		array(
			'type' => 'textarea',
			'rows' => 6,
			'label' => __('How to reproduce the issue'),
			'title' => __('How to reproduce step by step, copy/past the program url and occurence'),
			'placeholder' => __('How to reproduce step by step, copy/past the program url and occurence')));
	echo $this->Form->input('ReportIssue.screenshot', array('type' => 'file'));	
	echo $this->Form->button(__('Report'), array('class'=>'ttc-button', 'type' => 'submit'));
	echo $this->Form->button(__('Close'), array('class'=>'ttc-button', 'onclick'=> 'popupBrowserClose()'));	
	?>	
</div>