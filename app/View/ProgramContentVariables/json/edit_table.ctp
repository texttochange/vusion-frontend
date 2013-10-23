<?php
if ($this->validationErrors['ContentVariableTable']==array()) {
    $result = array("status"=>"ok");
} else {
    $result = array(
        "status" => "fail", 
        "reason" => $this->validationErrors['ContentVariableTable']);
}
echo $this->Js->object($result);
