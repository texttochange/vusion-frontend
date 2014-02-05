<?php
$result = array(
    'status' => 'ok',
    'paginationCount' => $paginationCount,
    'roundedCount' => $this->BigNumber->replaceBigNumbers($paginationCount, 3));
echo $this->Js->object($result);
