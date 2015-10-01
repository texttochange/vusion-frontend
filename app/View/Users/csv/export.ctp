<?php
$headers = array('username', 'email', 'group', 'invited_by');
echo $this->Csv->arrayToLine($headers);
foreach ($users as $user) {
	$data = array(
		$user['User']['username'],
		$user['User']['email'],
		$user['Group']['name'],
		$user['InvitedBy']['username']);
	echo $this->Csv->arrayToLine($data);
}
