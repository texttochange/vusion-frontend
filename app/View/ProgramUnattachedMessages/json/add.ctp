<?php
if ($savedUnattachedMessage) {
	$result = array(
    	'status' => 'ok',
    	'id' => $savedUnattachedMessage['UnattachedMessage']['_id'],
    	'name' => $savedUnattachedMessage['UnattachedMessage']['name']);
} else {
	$result = array(
    	'status' => 'fail',
    	'message' => $this->Session->read('Message.flash.message'));
	if (isset($this->validationErrors['UnattachedMessage'])) {
    	$result['validationErrors'] = $this->validationErrors['UnattachedMessage'];
	}
}
echo $this->Js->object($result);