<div>
	
	<?php echo $this->Html->tag('form', null, array(' id'=> 'dynamic-generic-program-form')); ?>
	<?php $this->Js->get("#dynamic-generic-program-form");
		$this->Js->each('$(this).buildTtcForm()', true); ?>	
</div>
<?php echo $this->Js->writeBuffer(); ?>
