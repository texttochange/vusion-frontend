<?php
if ($ajaxResult['paginationCount']) {
	echo ',"pagination-count":"' . $ajaxResult['paginationCount'] . '"';
    echo ',"rounded-count":"' . $this->BigNumber->replaceBigNumbers($ajaxResult['paginationCount'], 3) . '"';
}
