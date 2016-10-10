<?php
if ($this->validationErrors['Program'] != array()) {
	echo ',"validation-errors":' . $this->Js->object($this->validationErrors['Program']);
} else {
//echo ',"Uid":' . json_encode($savedProgram['Program']['name']);
echo ',"name":' . json_encode($savedProgram['Program']['name']);
/*echo ',"data":[';
$data = array();
	$data[] = '{"name":"' . $savedProgram['Program']['name'] .
	'","url":'. $this->Js->object($savedProgram['Program']['url']) .
    '}';
echo ']';

*/

}
