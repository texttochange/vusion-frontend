<div>
	
<div class="participants index">
    <h3>Import Participants</h3>
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
	                array('program'=> $programUrl,'controller' => 'programHome'));
                ?></li>
                <li><?php echo $this->Html->link(__('Add Participant'), array('program' => $programUrl, 'controller' => 'programParticipants', 'action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('View Participant(s)'), array('program' => $programUrl, 'controller' => 'programParticipants', 'action' => 'index'));?></li>
	</ul>
	
</div>
</div>
