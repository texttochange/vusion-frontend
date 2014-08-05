<?php
$this->element("ajax_headers");
echo '{"status":"' . ($requestSuccess? 'ok' : 'fail') . '","message":"'.$this->Session->read('Message.flash.message').'"}';
CakeSession::delete('Message.flash');