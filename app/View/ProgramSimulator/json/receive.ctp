<?php 
if ($message)
    echo $this->Js->object(array("status"=>"ok","message"=> $message));
else
    echo $this->Js->object(array("status"=>"ok"));
?>
