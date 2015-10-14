<?php
    $this->RequireJs->scripts(array("ttc-utils"));
?>
<?php if ($this->AclLink->_allow('controllers/ProgramLogs')) { ?>
<div id='notifications' class='ttc-notification'>
<?php
    foreach ($programLogsUpdates as $log) {
        $newDate = $this->Time->format('d/m/Y H:i:s', substr($log, 1, 19));
        echo substr_replace($log, "<span style='font-weight:bold'>".$newDate."</span>", 1, 19)."<br />";
    }
    $this->RequireJs->runLine(
        'setInterval(function(){pullBackendNotifications("'.$this->Html->url(
            array('program'=>$programDetails['url'], 'controller'=>'programLogs', 'action'=>'getBackendNotifications.json')).'")}, 10000);');
?>
</div>
<?php } ?>
