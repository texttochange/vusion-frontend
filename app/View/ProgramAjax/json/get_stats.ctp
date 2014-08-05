<?php
echo ',"program-url":"' . $programDetails['url']. '"';
$roundOffStats = $this->BigNumber->roundOffNumbers($programStats);
echo ',"program-stats":' . $this->Js->object($roundOffStats);
