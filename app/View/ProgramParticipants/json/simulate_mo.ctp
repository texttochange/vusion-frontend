<?php
if ($participant) {
	echo ',"phone":'. json_encode($participant['Participant']['phone']);
}
