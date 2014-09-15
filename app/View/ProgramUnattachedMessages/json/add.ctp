<?php
if ($this->validationErrors['UnattachedMessage'] != array()) {
    echo ',"validation-errors":' . $this->Js->object($this->validationErrors['UnattachedMessage']);
} else {
	echo ',"id":' . json_encode($savedUnattachedMessage['UnattachedMessage']['_id']);
    echo ',"name":' . json_encode($savedUnattachedMessage['UnattachedMessage']['name']);
} 