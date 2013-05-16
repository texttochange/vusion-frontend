<div class="participants index">
    <ul class="ttc-actions">
		<li><?php echo $this->Html->link(__('Add Participant'), array('program' => $programUrl, 'controller' => 'programParticipants', 'action' => 'add'), array('class'=>'ttc-button')); ?></li>
		<li><?php echo $this->Html->link(__('View Participant(s)'), array('program' => $programUrl, 'controller' => 'programParticipants', 'action' => 'index'), array('class'=>'ttc-button'));?></li>
	</ul>
    <h3>Import Participants</h3>
    <div class="ttc-display-area">
	<?php
	    echo $this->Form->create('Import', array('type' => 'file'));
		echo $this->Form->input('Import.file', array(
		    'between' => '<br />',
		    'type' => 'file'
		));
		echo $this->Form->input('tags', array('label' => __('Tag imported participants')));
		echo '<div>';
		echo $this->Form->checkbox('replace-tags-and-labels', array(
		    'label' => 'Update participant',
		    'value' => 'update',
		    'hiddenField' => false));
		echo $this->Html->tag('label',  _("If participant already in replace their tags and labels."));
		echo '</div>';
		echo $this->Form->end(__('Upload'));
	?>

	<div>
	   <?php 
	  if (isset($report) && $report!=false) {
	      $importFailed = array_filter($report, function($participant) { 
	              return (!$participant['saved']);
	      });
	      $updated = array_filter($report, function($participant) { 
	              return ($participant['saved'] && $participant['exist-before']);
	      });
	      if (count($importFailed) == 0) {
	          echo __("Import of %s participant(s) succeed.", count($report));
	      } else { 
	          echo __("Import failed for %s participant(s) over a total of %s participant(s).", count($importFailed), count($report));
	          echo "<br/>";
	          foreach($importFailed as $failure){ 
	              echo __("On line %s with number %s: %s", $failure['line'],  $failure['phone'], implode(", ", $failure['message']));
	              echo "<br/>";
	          }
	      }
	      if (count($updated) > 0) {
	          echo __(" %s of the successfull import(s) were only updated.", count($updated));
	      }
	  }
	  ?>
	  </div>
	  </div>
</div>
