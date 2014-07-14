<?php
if ($savedParticipant) {
	$result = array(
		'status' => 'ok',
		'phone' => $savedParticipant['Participant']['phone']);
} else {
	$result = array(
		'status' => 'fail',
		'message' => $this->Session->read('Message.flash.message'));
	if (isset($this->validationErrors['Participant'])) {
		$result['validation-errors'] = $this->validationErrors['Participant'];
	}
}
echo $this->Js->object($result);