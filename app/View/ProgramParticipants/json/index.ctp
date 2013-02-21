<?php
if (isset($programTimezone)) {
     $now = new DateTime('now');
     date_timezone_set($now,timezone_open($programTimezone));
     echo $this->Js->object(array(
         "results" => $participants,
         "program-time" => $now->format(DateTime::ISO8601)));
     return;
} else {
    echo $this->Js->object(array("results" => $participants));
};
?>	
