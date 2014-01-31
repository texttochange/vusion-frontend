<div  class='ttc-program-stats-inside'>
<?php
$this->Js->get('document')->event(
    'ready',
    'loadProgramStatsInsideProgram();             
    ');

echo '<span id="programstats" style="font-weight: bold">';
echo '<img src="/img/ajax-loader.gif">';
echo '</span>';
if ($creditStatus['manager']['status'] != 'none') {    
    echo '<span class="stat" title="Credit Remaining/Total">';
    echo '<img src="/img/credit-icon-16.png" height="13px" width="14px"> ';
    $creditUsed     = $creditStatus['count'];
    $totalCreditSet = $programDetails['settings']['credit-number'];
    $creditLeft     = $totalCreditSet - $creditUsed;
    $now = new DateTime('now');
    date_timezone_set($now,timezone_open($programDetails['settings']['timezone']));
    $programTimeNow = strtotime($now->format("Y-m-d"));
    $creditEndDate  = strtotime($programDetails['settings']['credit-to-date']);
    $timeLeft       = $creditEndDate - $programTimeNow;
    $daysToDeadLine = $timeLeft/(60*60*24);
    if($daysToDeadLine < 7) {
        echo '<span style="font-weight: bold">'.$creditLeft.'/'. $totalCreditSet.' '.$daysToDeadLine.'</span> day(s) left';
        
    } else {
        $creditEndDateSet = date('Y-m-d', $creditEndDate);
        echo '<span style="font-weight: bold">'.$creditLeft.'/'. $totalCreditSet.'</span> Until <span style="font-weight: bold">'.$creditEndDateSet.'</span>';
    }
    echo '</span>';
}
?>
</div>
