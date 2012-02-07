<div>
	<h2><?php echo __('Script').' of '.$programName.' program';?></h2>
	<?php echo $this->Html->tag('div', 'The script has been activated', array(' id'=> 'ttc-text')); ?>
</div>
<?php echo $this->Js->writeBuffer(); ?>
