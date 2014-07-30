<?php
echo $this->Html->tag('div', null, array('class'=>'ttc-report-issue-container'));
echo $this->Html->tag('h2', __('Report Issue'));
echo $this->Form->create('ReportIssue', array('type' => 'file', 'url' => array('controller' => 'users', 'action' =>'reportIssue')));
echo $this->Html->tag('span', __('<b>NB.</b>
    When reporting Vusion issues <b>Please</b> add the following details in your report:  
        <ul>
            <li> Describe the issue ie.<i> expected and now results</i></li>
            <li> How to reproduce the issue ie. <i> steps that lead to the issue.</i></li>
            <li> Provide <b>URL</b> to Vusion your program.</li>
            <li> Occurence ie. <i>All the time, once in a while.</i></li>            
            <li>Attach screenshots.</li>
        </ul>    
    '));
echo $this->Form->textarea('text', array('placeholder' => __('Message'), 'id' => 'reportIssueMessage', 'name' => 'reportIssueMessage', 'class' => 'report-message'));
echo $this->Form->input('ReportIssue.Screenshort', array('type' => 'file'));
echo $this->Form->end(array('label' => __('Send')));
?>
