<?php
$nvd3Friendly = array(
	array(
	    'key' => 'opt-in',
	    'values' => array()),
	array(
	    'key' => 'opt-out',
    	'values' => array()));
foreach ($stats as $stat) {
    $nvd3Friendly[0]['values'][] = array(
        'x' => $stat['ParticipantStats']['_id'],
        'y' => $stat['ParticipantStats']['value']['opt-in']);
    $nvd3Friendly[1]['values'][] = array(
        'x' => $stat['ParticipantStats']['_id'],
        'y' => $stat['ParticipantStats']['value']['opt-out']);
}
echo ',"data":' . $this->Js->object($nvd3Friendly);