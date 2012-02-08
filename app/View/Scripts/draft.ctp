<div>
	<h2><?php echo __('Draft Script').' of '.$programName.' program';?></h2>
<div class="index">
	<h3><?php echo __('Script'); ?></h3>
	<?php echo $this->Html->tag('form', null, array(' id'=> 'dynamic-generic-program-form')); ?>
	
	<?php	$this->Js->get("#dynamic-generic-program-form");
		$this->Js->each('$(this).buildTtcForm('.$this->Js->object($script).')', true); 
		?>	
</div>
<div class='actions'>
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Program Homepage'), array('program'=>$programUrl,'controller'=>'home'), array('class'=>'ttc-button')); ?></li>
		<li><?php echo $this->Html->tag('div', __('Save as draft'), array('class'=>'ttc-button', 'id' => 'button-save')); ?></li>
		<?php $this->Js->get('#button-save')->event('click', 'saveFormOnServer()' , true); ?>
	</ul>
</div>
<?php echo $this->Js->writeBuffer(); ?>
