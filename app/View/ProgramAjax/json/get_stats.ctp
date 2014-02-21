<?php
    if(isset($programStats)){
        foreach ($programStats as $key => $value) {
                $result = $this->BigNumber->replaceBigNumbers($value, 3);
                $programStats['programStats'][$key] = $result;
        }
        $response = array('status' =>'ok', 'programUrl' => $programUrl, 'programStats' => $programStats);
    }
    echo $this->Js->object($response);
?>	
