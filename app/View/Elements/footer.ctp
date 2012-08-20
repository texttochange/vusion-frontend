<?php
	echo $this->Html->tag('div', 'See Database Dump', array('id'=>'show-database-dump', 'style'=>'color:black;background:white'));
	$this->Js->get('#show-database-dump')->event(
	    'click',
	    '$("#sql_dump").show()');
	?>
<div id='sql_dump' style='display:none'>
	<?php echo $this->element('sql_dump'); ?>
</div>
