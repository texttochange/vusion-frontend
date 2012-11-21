<div class="participants index">
    <ul class="ttc-actions">
		<li><?php echo $this->Html->link(__('Add Participant'), array('program' => $programUrl, 'controller' => 'programParticipants', 'action' => 'add'), array('class'=>'ttc-button')); ?></li>
		<li><?php echo $this->Html->link(__('View Participant(s)'), array('program' => $programUrl, 'controller' => 'programParticipants', 'action' => 'index'), array('class'=>'ttc-button'));?></li>
	</ul>
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
	   if (isset($entries) && is_array($entries)) {
	       foreach($entries as $entry){ 
	           echo $entry."<br />";
	       }
	   } else {
	       echo "$entries";
	   }
	   ?>
	</div>

</div>
