<div class="powered-by">
<?php
echo $this->Html->tag('span', __("Vusion %s powered by", Configure::read('vusion.version')), array('class' => 'powered-by-text'));
echo $this->Html->image('connect4change-logo.png', array('class' => 'powered-by-logo', 'url' => 'http://connect4change.nl' ));
echo $this->Html->image('ttc-logo.png', array('class' => 'ttc-logo','url' => 'http://texttochange.org/vusion'));
?>  
</div>
<?php 
if ($this->_getElementFilename('database_dump')) {
    echo $this->element('database_dump'); 
}
?>