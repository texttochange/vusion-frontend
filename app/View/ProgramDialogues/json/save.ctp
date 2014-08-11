<?php 
if ($this->validationErrors['Dialogue']!=array()) {
	echo ',"validation-errors":{"Dialogue":'.$this->Js->object($this->validationErrors['Dialogue']).'}';
}
if (isset($dialogueObjectId)) {
	echo ',"dialogue-obj-id":"'.$dialogueObjectId.'"';
}
