<?php
if ($this->validationErrors['ContentVariable']!=array()) {
	echo ',"validation-errors":' . $this->Js->object($this->validationErrors['ContentVariable']['value']);
}
