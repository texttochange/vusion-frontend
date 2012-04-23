<?php 
if ($programLogs) {
    $newLogs = array();
    foreach ($programLogs as $log) {
        $newDate = $this->Time->format('d/m/Y H:i:s', substr($log, 1, 19));
        $newLogs[] = substr_replace($log, $newDate, 1, 19);
    }
    echo $this->Js->object(array("status"=>"ok","logs"=>array_reverse($newLogs)));
}
else {
    echo $this->Js->object(array("status"=>"ok"));
}
?>
