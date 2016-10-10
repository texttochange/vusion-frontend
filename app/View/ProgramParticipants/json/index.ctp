<?php
if (isset($participants)){
    echo ',"data":' . $this->Js->object($participants);
} else {
    echo ',"data":' . $this->Js->object($participantSurveyProfileList);
}
