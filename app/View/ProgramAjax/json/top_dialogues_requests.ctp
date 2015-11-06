<?php
echo ',"data":[';
$data = array();
foreach ($stats as $stat) {
	$data[] = '{"name":"' . $stat['key'] .
	'","values":'. $this->Js->object($stat['values']) .
    '}';
}
echo implode(',', $data);
echo ']';
    
