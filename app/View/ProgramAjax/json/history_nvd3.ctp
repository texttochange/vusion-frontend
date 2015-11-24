<?php
$nvd3Friendly = array(
    array(
        'key'=> __('received'),
        'values' => array()),
    array(
        'key'=> __('sent'),
        'values' => array()));
foreach ($stats as $stat) {
	if (isset($stat['HistoryStats']['incoming'])) {
		$nvd3Friendly[0]['values'][] = array(
	        'x' => $stat['HistoryStats']['_id'],
	        'y' => $stat['HistoryStats']['incoming']);
	}
	if (isset($stat['HistoryStats']['outgoing'])) {
	    $nvd3Friendly[1]['values'][] = array(
	        'x' => $stat['HistoryStats']['_id'],
	        'y' => $stat['HistoryStats']['outgoing']);
	}
}
echo ',"data":' . $this->Js->object($nvd3Friendly);