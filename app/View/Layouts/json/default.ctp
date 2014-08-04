<?php
$this->element("ajax_headers");
if (!isset($ajaxResult)) {
	echo $content_for_layout;  // default behavior
} else {
	echo '{"status":"'.$ajaxResult['status'].'"';
	if ($this->Session->check('Message.flash.message')) { 
		echo ',"message":"'. json_encode($this->Session->read('Message.flash.message')).'"';
	}
	if (isset($programDetails['settings']['timezone'])) {
	     $now = new DateTime('now');
	     date_timezone_set($now,timezone_open($programDetails['settings']['timezone']));
	     echo ',"program-time":"' . $now->format(DateTime::ISO8601) . '"';
	}
	echo $content_for_layout;
	echo "}";
	CakeSession::delete('Message.flash');
}
?>	
