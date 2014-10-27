<div class="powered-by">
<?php
$vusionVersion = Configure::read('vusion.version');
echo $this->Html->tag('span', "Vusion $vusionVersion powered by", array('class' => 'powered-by-text'));
echo '<a href= "http://connect4change.nl" target = "_blank">';
echo '<img src = "/img/connect4change-logo.png"  class = "powered-by-logo">';
echo '</a>';
echo '<a href= "http://www.ttcmobile.com/" target = "_blank">';
echo '<img src = "/img/ttc-logo2.png"  class = "ttc-logo">';
echo '</a>';
?>  
</div>
<?php
if (Configure::read('debug') == 2) {
    echo $this->element('database_dump');
} 
?>