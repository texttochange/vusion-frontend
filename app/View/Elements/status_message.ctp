<table class="status-table" cellpadding="0" cellspacing="0" align="center">
<tr><td class="flash-box">
<?php
echo $this->Html->tag('div', '', array(
    'id' => 'connectionState',
    'class' => 'connection-message',
    'style' => 'display: none')
    );			
?>
</div>
</td></tr>
<tr><td class="flash-box">
<?php 
echo $this->Session->flash(); 
if (!$this->Session->flash()) {
    echo $this->Html->tag('div', '', array(
        'id' => 'flashMessage', 
        'class' => 'message', 
        'style' => 'display: none')
        );
}
?>
</td></tr>
<tr><td class="flash-box">
<?php
## Flash message for the credit manager's status
if (isset($creditStatus)) {
    echo $this->CreditManager->flash($creditStatus, (isset($programDetails['settings']) ? $programDetails['settings'] : null));
}
?>
<div>
</td></tr>
</table>
