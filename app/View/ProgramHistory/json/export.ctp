<?php 
if (isset($ajaxResult['fileName'])) {
    echo ',"file":' . json_encode($ajaxResult['fileName']);   
}