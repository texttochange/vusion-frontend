<?php
echo ',"program-url":"' . $ajaxResult['programUrl']. '"';
$roundOffStats = $this->BigNumber->roundOffNumbers($ajaxResult['programStats']);
echo ',"program-stats":' . $this->Js->object($roundOffStats);
