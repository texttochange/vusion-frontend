<?php
if ($this->validationErrors['Participant'] != array()) {
	echo ',"validation-errors":' . $this->Js->object($this->validationErrors['Participant']);
} else {
	echo ',"phone":' . json_encode($savedParticipant['Participant']['phone']);
}