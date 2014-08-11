<?php
$this->element("ajax_headers");
echo '{"status":"'. ( $requestSuccess ? 'ok': 'fail' ) .'"';
if ($this->Session->check('Message.flash.message')) { 
	echo ',"message":'. json_encode($this->Session->read('Message.flash.message')).'';
}
if (isset($programDetails['settings']['timezone'])) {
     $now = new DateTime('now');
     date_timezone_set($now,timezone_open($programDetails['settings']['timezone']));
     echo ',"program-time":' . json_encode($now->format(DateTime::ISO8601)) . '';
}
echo $content_for_layout;
echo "}";
CakeSession::delete('Message.flash');
