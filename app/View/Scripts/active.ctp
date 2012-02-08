<div>
	<h2><?php echo __('Active Script').' of '.$programName.' program';?></h2>
	<?php echo $this->Html->tag('form', null, array(' id'=> 'dynamic-generic-program-form')); ?>
	
	<?php	$this->Js->get("#dynamic-generic-program-form");
		$this->Js->each('$(this).buildTtcForm('.$this->Js->object($script).')', true); 
		?>	
</div>
<?php echo $this->Js->writeBuffer(); ?>
