<?php
if ($success) {
	$result = array(
		'status' => 'ok',
		'phone' => $participant['Participant']['phone']);
} else {
	$result = array(
		'status' => 'fail',
		'message' => $this->Session->read('Message.flash.message'));
}
echo $this->Js->object($result);