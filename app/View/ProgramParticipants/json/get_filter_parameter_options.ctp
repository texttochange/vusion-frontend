<?php
if (isset($results)) {
	echo ',"data":'. $this->Js->object($results);
}