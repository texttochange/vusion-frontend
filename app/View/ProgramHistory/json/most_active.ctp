<?php
echo ',"data":[' .
	'{"name":"dialogue","values":'.
	$this->Js->object($dialogueActivities) .
    '},'. 
    '{"name":"request","values":'.
    $this->Js->object($requestActivities).
    '}]';
