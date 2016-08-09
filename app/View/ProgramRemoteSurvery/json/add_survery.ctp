<?php
if ($this->validationErrors['Program'] != array()) {
	echo ',"validation-errors":' . $this->Js->object($this->validationErrors['Program']);
} 
