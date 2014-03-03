<div  class='ttc-program-stats-inside'>
<?php
$this->Js->set('programs', array(array('Program' => $programDetails)));
if (count($programStats['programStats']) <= 0) {   
    $this->Js->get('document')->event(
        'ready',
        'loadProgramStats();');
} else {
    $programStats['programStats'] = $this->BigNumber->roundOffNumbers($programStats['programStats']);
    $this->Js->get('document')->event(
        'ready',
        'renderStats('.$this->Js->object($programStats).')');
}

echo '<span id="programstats">';
echo '<img src="/img/ajax-loader.gif">';
echo '</span>';
if ($creditStatus['manager']['status'] != 'none') {    
    echo '<span>';
    echo '<table class="stat">';
    echo '<td><img src="/img/credit-icon-16.png"></td><td>';
    $creditUsed     = $creditStatus['count'];
    $totalCreditSet = $programDetails['settings']['credit-number'];
    $creditLeft     = $totalCreditSet - $creditUsed;
    $now = new DateTime('now');
    date_timezone_set($now,timezone_open($programDetails['settings']['timezone']));
    $programTimeNow = strtotime($now->format("Y-m-d"));
    $creditEndDate  = strtotime($programDetails['settings']['credit-to-date']);
    $timeLeft       = $creditEndDate - $programTimeNow;
    $daysToDeadLine = $timeLeft/(60*60*24);
    if ($daysToDeadLine < 7) {
        echo '<span title="Credit Remaining">'.
        $this->BigNumber->replaceBigNumbers($creditLeft, 3).' '.
        $daysToDeadLine.'</span> day(s) left';
        
    } else {
        $creditEndDateSet = date('d/m/Y', $creditEndDate);
        echo '<span title="Credit Remaining">'.$this->BigNumber->replaceBigNumbers($creditLeft, 3).' until '.
        $creditEndDateSet.'</span>';
    }
    echo '</td></table>';
    echo '</span>';
    
}
?>
</div>
