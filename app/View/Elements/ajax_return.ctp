<?php
$this->element("ajax_headers");
echo '{"status":"' . $ajaxResult['status'] . '","message":"'.$this->Session->read('Message.flash.message').'"}';
CakeSession::delete('Message.flash');