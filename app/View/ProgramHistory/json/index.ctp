<?php
if (isset($programDetails['settings']['timezone'])) {
     $now = new DateTime('now');
     date_timezone_set($now,timezone_open($programDetails['settings']['timezone']));
     echo $this->Js->object(array(
         "results" => $statuses,
         "program-time" => $now->format(DateTime::ISO8601)));
     return;
}
echo $this->Js->object(array("results" => $statuses));
?>	
