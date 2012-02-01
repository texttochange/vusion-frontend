<div>
	<h2><?php echo __('Home').' of '.$programName.' program';?></h2>
	<?php if ($programActive && $programDraft) {
		echo $this->Html->tag('div', 'No script has been defined for this program');
		echo $this->Html->tag('button', 'Create script', array('id'=>'create-script'));
		$this->Js->get('#create-script');
		$this->Js->event('click', '$("#dynamic-generic-program-form").empty().buildForm(fromBackendToFrontEnd())');
		} ?>
	<?php echo $this->Html->tag('div', 'Number of participants: '.$participantCount); ?>
	<?php echo $this->Html->tag('from', null, array(' id'=> 'dynamic-generic-program-form')); ?>
	
</div>
<?php echo $this->Js->writeBuffer(); ?>
