<table class="status-table" cellpadding="0" cellspacing="0" align="center">
<tr><td>
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
<tr><td>
<?php
echo $this->Html->tag('div', '', array(
    'id' => 'connectionState',
    'class' => 'connection-message',
    'style' => 'display: none')
    );			
?>
</td></tr>
<?php
## Flash message for the credit manager's status
if ($creditStatus != null) {
    $since = $this->Time->niceShort($creditStatus['since']);
    switch ($creditStatus['status']) {
        case 'no-credit':
            $message = __("You program has exceed the number of message send since %s.", $since);
            break;
        case 'no-credit-timeframe':
            $message = __("You program is not allow to send sms at the curent day since %s.", $since);
            break;
        case 'none':
            break;
        case 'ok':
            break;
        default:
            $message = __("Unknown status: %s.", $creditStatus['status']);
            break;
    }
    if (isset($message)) {
        echo "<tr><td>";
        echo $this->Html->tag('div', $message, 
            array(
                'id' => 'creditStatus',
                'class' => 'message credit-message')
            );			
        echo "</td></tr>";
    }
}
?>
</table>
