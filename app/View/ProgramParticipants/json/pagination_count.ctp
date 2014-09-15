<?php
if ($paginationCount) {
	echo ',"pagination-count":"' . $paginationCount . '"';
    echo ',"rounded-count":"' . $this->BigNumber->replaceBigNumbers($paginationCount, 3) . '"';
}
