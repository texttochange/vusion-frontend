<?php
if(isset($programStats)){
    $myHelper = $this->BigNumber;
    function roundOffStats(&$value, $key, $myHelper)
    {
        $value= array('exact-count' => $value, 'round-count' => $myHelper->replaceBigNumbers($value, 3));
    }
    array_walk($programStats, 'roundOffStats', $myHelper);
    $response = array('status' =>'ok', 'programUrl' => $programUrl, 'programStats' => $programStats);
} else {
    $response = array('status' =>'ok', 'programUrl' => $programUrl, 'programStats' => null);
}

    echo $this->Js->object($response);
?>	
