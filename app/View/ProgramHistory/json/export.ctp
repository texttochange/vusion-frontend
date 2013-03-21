<?php 
    if (isset($errorMessage)) {
        echo $this->Js->object(array("status"=>"fail", "message" => $errorMessage));
        return;
    }
    echo $this->Js->object(array("status" => "ok","file" => $fileName));   
?>