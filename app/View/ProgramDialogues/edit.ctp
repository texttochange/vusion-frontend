<div class="index">
	<h3><?php 
	if (isset($dialogue)) 
	    echo __('Edit Dialogue'); 
	else
	    echo __('Create Dialogue');
	?></h3>
	<?php
	if (isset($dialogue) && !$dialogue['Dialogue']['activated'])  
	    	    echo $this->Html->tag('span', __('(editing the draft)', array('class'=>'ttc-dialogue-draft'))); 
	?>
    <ul class="ttc-actions">
		<li><?php echo $this->Html->tag('div', __('Save'), array('class'=>'ttc-button', 'id' => 'button-save')); ?></li>
		<?php $this->Js->get('#button-save')->event('click', 'saveFormOnServer()' , true);?> 
        <?php 
        if (isset($dialogue)) {
            if (!$dialogue['Dialogue']['activated']) {
                echo "<li>";
                echo $this->Html->link(__('Activate'), array('program'=>$programUrl,'action'=>'activate', 'id'=>$dialogue['Dialogue']['_id']), array('class'=>'ttc-button', 'id' => 'button-test'));
                echo "</li>";
            } 
            echo "<li>";
            echo $this->Html->link(__('Simulate'), array('program'=>$programUrl,'action'=>'simulate', 'id'=>$dialogue['Dialogue']['_id']), array('class'=>'ttc-button', 'id' => 'button-test'));
            echo "</li>";
            echo "<li>";
            echo $this->Html->link(__('Test send all messages'), array('program'=>$programUrl,'action'=>'testSendAllMessages', 'id'=>$dialogue['Dialogue']['_id']), array('class'=>'ttc-button', 'id' => 'button-test'));
            echo "</li>"; 
        }?>
	</ul><br /><br />
	<div class="ttc-display-area">
	<?php echo $this->Html->tag('form', null, array(' id'=> 'dynamic-generic-program-form')); ?>
	
	<?php
        $this->Js->get("#dynamic-generic-program-form");
        if (isset($dialogue))
            $this->Js->each('$(this).buildTtcForm('.$this->Js->object(array('dialogue' => $dialogue['Dialogue'])).')', true);
        else
            $this->Js->each('$(this).buildTtcForm()', true);
	?>
	</div>

<?php echo $this->Js->writeBuffer(); ?>
