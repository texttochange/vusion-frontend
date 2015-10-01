<?php
if (isset($participant['Participant'])) {
	echo ',"participant":'. json_encode($participant['Participant']);
	echo ',"histories":'. json_encode($histories);
	echo ',"schedules":'. json_encode($schedules);
}