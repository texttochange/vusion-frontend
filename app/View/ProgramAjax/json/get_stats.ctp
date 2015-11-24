<?php
echo ',"program-url":"' . $programDetails['url']. '"';
$roundOffStats = $this->BigNumber->roundOffNumbers($stats);
echo ',"program-stats":' . $this->Js->object($roundOffStats);
