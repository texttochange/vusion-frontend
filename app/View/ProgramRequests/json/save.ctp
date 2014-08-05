<?php 
if ($this->validationErrors['Request']!=array()) {
	echo ',"validation-errors":'.$this->Js->object(array('Request' => $this->validationErrors['Request']));
}
if (isset($savedRequest)) {
	echo ',"request-id":'. json_encode($savedRequest['Request']['_id']);
}