<div>
<?php
echo $this->Html->tag('span', 'Vusion powered by');
echo $this->Html->image('connect4change-logo.png', array('class' => 'powered-by-logo'));
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
