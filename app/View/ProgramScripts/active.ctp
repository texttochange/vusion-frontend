<div class="index">
    <ul class="ttc-actions">
		<li><?php echo $this->Html->tag('div', __('Save as draft'), array('class'=>'ttc-button', 'id' => 'button-save')); ?></li>
		<?php $this->Js->get('#button-save')->event('click', 'saveFormOnServer()' , true); ?>
		<li><?php echo $this->Html->link(__('Test send all messages'), array('program'=>$programUrl,'action'=>'testSendAllMessages'), array('class'=>'ttc-button', 'id' => 'button-test')); ?></li>
		
	</ul>
	<br /><br /><br />
	<h3><?php echo __('Script'); ?></h3>
	<?php echo $this->Html->tag('form', null, array(' id'=> 'dynamic-generic-program-form')); ?>
	
	<?php	$this->Js->get("#dynamic-generic-program-form");
		$this->Js->each('$(this).buildTtcForm('.$this->Js->object($script).')', true); 
		?>	


<?php echo $this->Js->writeBuffer(); ?>
