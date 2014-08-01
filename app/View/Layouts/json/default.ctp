<?php
$this->element("ajax_headers");
if (!isset($ajaxResult)) {
	echo $content_for_layout;  // default behavior
} else {
	echo '{"status":"'.$ajaxResult['status'].'"';
	if ($this->Session->check('Message.flash.message')) { 
		echo ',"message":"'.$this->Session->read('Message.flash.message').'"';
	}
	echo $content_for_layout;
	echo "}";
	CakeSession::delete('Message.flash');
}
?>	
