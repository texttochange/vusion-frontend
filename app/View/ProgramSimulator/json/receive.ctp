<?php 
if ($message)
    echo $this->Js->object(array("status"=>"ok","message"=>"a message"));
else
    echo $this->Js->object(array("status"=>"ok"));
?>
