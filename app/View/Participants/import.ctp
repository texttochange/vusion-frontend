<div>
	<h2><?php echo __('Participants').' of '.$programName.' program';?></h2>
<div class="participants index">
	<?php
	//echo $this->Form->create('Import', array('enctype' => 'multipart/form-data'));	
	echo $this->Form->create('Import', array('type' => 'file'));
		echo $this->Form->input('Import.file', array(
		    'between' => '<br />',
		    'type' => 'file'
		));
		echo $this->Form->end(__('Upload'));
	?>

	<div>
	   <?php 
	   if (isset($entries)) {
	       foreach($entries as $entry){ 
	           echo $entry."<br />";
	       }
	   }
	   ?>
	</div>

</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Back To Program Home'),
	                array('program'=> $programUrl,'controller' => 'home'));
                ?></li>
                <li><?php echo $this->Html->link(__('Add Participant'), array('program' => $programUrl, 'controller' => 'participants', 'action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('View Participant(s)'), array('program' => $programUrl, 'controller' => 'participants', 'action' => 'index'));?></li>
	</ul>
	
</div>
</div>
