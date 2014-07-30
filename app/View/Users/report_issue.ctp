<?php
echo $this->Html->tag('div', null, array('class'=>'ttc-report-issue-container'));
echo $this->Html->tag('h2', __('Report Issue'));
echo $this->Form->create('ReportIssue', array('url' => array('controller' => 'users', 'action' =>'reportIssue')));
echo $this->Html->tag('span', __('<h1><b>Note</b></h1>
    <p>When reporting Vusion issues Please add the following details in your report:<br>
     1. How to reproduce the issue ie <i>URL, step that lead to the issue. </i><br>
     2. Occurence ie <i>All the time, once in a while.</i><br>
     3. Attach screenshots. <br>
    </p>'));
echo $this->Form->textarea('text', array('placeholder' => __('Message'), 'id' => 'reportIssueMessage', 'name' => 'reportIssueMessage', 'class' => 'report-message'));
echo $this->Form->input('Screenshort', array('type' => 'file', 'id' => 'attachments'));
echo $this->Form->end(array('label' => __('Send')));
?>
