<?php
$headers = array('timestamp', 'timezone', 'user', 'program', 'event');
echo $this->Csv->arrayToLine($headers);
foreach ($userLogs as $userLog) {
	$data = array(
		'timestamp' => $userLog['UserLog']['timestamp'],
		'timezone' => $userLog['UserLog']['timezone'],
		'user' => $userLog['UserLog']['user-name'],
		'event' => $userLog['UserLog']['parameters']);
	if (isset($userLog['UserLog']['program-name'])) {
		$data['program-name'] = $userLog['UserLog']['program-name'];
	}
	echo $this->Csv->dictToLine($data, $headers);
}
