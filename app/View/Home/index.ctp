<div>
	<h2><?php echo __('Home');?></h2>
	<?php echo $this->Html->tag('from', null, array(' id'=> 'dynamic-generic-program-form')); ?>
	<?php $this->Js->get('#dynamic-generic-program-form'); 
		$this->Js->each('$(this).buildForm(fromBackendToFrontEnd());', true);
	?>
</div>
<?php echo $this->Js->writeBuffer(); ?>
