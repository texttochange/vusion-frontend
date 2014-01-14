<div class="powered-by">
<?php
echo $this->Html->tag('span', 'Vusion powered by', array('class' => 'powered-by-text'));
echo $this->Html->image('connect4change-logo.png', array('class' => 'powered-by-logo', 'url' => 'http://connect4change.nl' ));
echo $this->Html->image('ttc-logo.png', array('class' => 'ttc-logo','url' => 'http://texttochange.org/vusion'));
?>  
</div>
<?php echo $this->element('database_dump'); ?>