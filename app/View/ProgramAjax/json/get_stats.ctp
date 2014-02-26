<?php
if(isset($programStats)){
    $roundOffStats = $this->BigNumber->roundOffNumbers($programStats);
    $response      = array('status' =>'ok', 'programUrl' => $programUrl, 'programStats' => $roundOffStats);
} else {
    $response = array('status' =>'ok', 'programUrl' => $programUrl, 'programStats' => null);
}

    echo $this->Js->object($response);
?>	
