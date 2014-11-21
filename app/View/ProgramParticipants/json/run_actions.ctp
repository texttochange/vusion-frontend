<?php
if (isset($validationErrors)) {
	echo ',"validation-errors":' . $this->Js->object($validationErrors);
} 