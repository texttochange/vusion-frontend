<?php
    if(isset($programStats)){
        $response = array('status' =>'ok', 'programUrl' => $programUrl, 'programStats' => $programStats);
    }else{
        $response = array('status' =>'fail', 'programUrl' => $programUrl, 'reason' => "This program url ". $programUrl." doesn't exist", 'programStats' => null);
    }
    echo $this->Js->object($response);
?>	
