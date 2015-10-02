<?php
    $this->RequireJs->runLine('$("[class*=success]").delay(5000).fadeOut(1000);');
?>
<div class="status-table table">
    <div class="flash-box row">
    <?php
    echo $this->Html->tag('div', '', array(
        'id' => 'connectionState',
        'class' => 'connection-message',
        'style' => 'display: none'));
    ?>
    </div>
    <div class="flash-box row">
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
    <div class="flash-box row">
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
