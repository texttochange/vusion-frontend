<?php 
foreach ($ajaxResult['programLogs'] as &$log) {
    $newDate = $this->Time->format('d/m/Y H:i:s', substr($log, 1, 19));
    $log = substr_replace($log, $newDate, 1, 19);
}
echo ',"logs":' . $this->Js->object($ajaxResult['programLogs']);
