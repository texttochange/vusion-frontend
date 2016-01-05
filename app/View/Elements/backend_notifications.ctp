<?php
    $this->RequireJs->scripts(array("ttc-utils", "ttc-backend-notification"));
?>
<?php if ($this->AclLink->_allow('controllers/ProgramLogs')) { ?>
<div id='notifications' class='ttc-notification'>
    <img src="/img/ajax-loader.gif" class="simulator-image-load"></img>
</div>
<?php
    $this->RequireJs->runLine(        
    '$("#notifications").pullBackend("'.$this->Html->url(
        array('program'=>$programDetails['url'], 'controller'=>'programLogs', 'action'=>'getBackendNotifications.json')).'");'
    );
} ?>
