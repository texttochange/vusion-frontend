<div class="powered-by">
<?php
echo $this->Html->tag('span', 'Vusion powered by', array('class' => 'powered-by-text'));
echo $this->Html->image('connect4change-logo.png', array('class' => 'powered-by-logo'));
echo $this->Html->image('TTC_Logo_web_2.png', array('class' => 'ttc-logo','url' => 'http://texttochange.org/vusion'));

?>  
</div>
<?php
if ($this->Js) {
        echo $this->Html->tag('div', 'See Database Dump', array('id'=>'show-database-dump', 'style'=>'color:black;background:white'));
       
	    $this->Js->get('#show-database-dump')->event(
	        'click',
	        '$("#sql_dump").show()');
}
?>
<div id='sql_dump' style=<?php if ($this->Js) echo 'display:none';?> >
<?php 
echo $this->element('sql_dump'); ?>
</div>
