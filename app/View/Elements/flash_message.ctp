<?php
    $this->RequireJs->runLine('$("[class*=success]").delay(5000).fadeOut(1000);');
?>
<div class="status-message <?php echo ($asTable? 'row' :'')  ?>"> 
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