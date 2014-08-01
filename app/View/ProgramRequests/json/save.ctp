<?php 
if ($this->validationErrors['Request']!=array()) {
	echo ',"validation-errors":{"Request":'.$this->Js->object($this->validationErrors['Request']).'},';
}
if (isset($ajaxResult['request-id'])) {
	echo ',"request-id":"'.$ajaxResult['request-id'].'"';
}