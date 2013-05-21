<?php if ($this->AclLink->_allow('controllers/ProgramLogs')) { ?>
<div id='notifications' class='ttc-notification'>
<?php
    if($hasProgramLogs) {
        foreach ($programLogsUpdates as $log) {
            $newDate = $this->Time->format('d/m/Y H:i:s', substr($log, 1, 19));
            echo substr_replace($log, "<span style='font-weight:bold'>".$newDate."</span>", 1, 19)."<br />";
        }
    }
    $this->Js->get('document')->event(
        'ready',
        'setInterval(function(){pullBackendNotifications("'.$this->Html->url(
            array('program'=>$programUrl, 'controller'=>'programLogs', 'action'=>'getBackendNotifications.json')).'")}, 10000);');
?>
</div>
<?php } ?>
