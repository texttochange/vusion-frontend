<?php
    $this->RequireJs->scripts(array("ttc-utils", "ttc-backend-notification"));
?>
<?php if ($this->AclLink->_allow('controllers/ProgramLogs')) { ?>
<div id='notifications' class='ttc-notification'>
<?php
    foreach ($programLogsUpdates as $log) {
        $newDate = $this->Time->format('d/m/Y H:i:s', substr($log, 1, 19));
        echo substr_replace($log, "<span style='font-weight:bold'>".$newDate."</span>", 1, 19)."<br />";
    }
?>
</div>
<?php
    $this->RequireJs->runLine(        
    '$("#notifications").pullBackend("'.$this->Html->url(
        array('program'=>$programDetails['url'], 'controller'=>'programLogs', 'action'=>'getBackendNotifications.json')).'");'
    );
} ?>
