<?php
if ($this->validationErrors['ContentVariable']==array()) {
    $result = array("status"=>"ok");
} else {
    $result = array(
        "status" => "fail", 
        "reason" => $this->validationErrors['ContentVariable']);
}
echo $this->Js->object($result);
