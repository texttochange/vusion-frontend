<?php
if (isset($programTimezone)) {
     $now = new DateTime('now');
     date_timezone_set($now,timezone_open($programTimezone));
     echo $this->Js->object(array(
         "results" => $statuses,
         "program-time" => $now->format(DateTime::ISO8601)));
     return;
}
echo $this->Js->object(array("results" => $statuses));
?>	
