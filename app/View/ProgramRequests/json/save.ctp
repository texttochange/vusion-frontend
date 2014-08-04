<?php 
if ($this->validationErrors['Request']!=array()) {
	echo ',"validation-errors":{"Request":'.$this->Js->object($this->validationErrors['Request']).'},';
}
if (isset($ajaxResult['requestId'])) {
	echo ',"request-id":"'.$ajaxResult['request-id'].'"';
}