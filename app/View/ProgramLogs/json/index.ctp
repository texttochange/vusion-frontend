<?php 
    if ($programLogs)
     	echo $this->Js->object(array("status"=>"ok","logs"=>$programLogs));
    else
        echo $this->Js->object(array("status"=>"ok"));
?>
