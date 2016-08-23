<?php
if ($this->validationErrors['Program'] != array()) {
	echo ',"validation-errors":' . $this->Js->object($this->validationErrors['Program']);
} else {
echo ',"Uid":' . json_encode($savedProgram['Program']['name']);
}
