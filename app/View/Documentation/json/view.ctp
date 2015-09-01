<?php 
if ($requestSuccess) {
	echo ',"topic":"' . $topic . '"';
	echo ',"documentation":' . $this->Js->object($documentation);
}
?>	
