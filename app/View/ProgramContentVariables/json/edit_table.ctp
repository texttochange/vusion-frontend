<?php
if ($this->validationErrors['ContentVariableTable']!=array()) {
   	echo ',"validation-errors":'. $this->Js->object($this->validationErrors['ContentVariableTable']);
}