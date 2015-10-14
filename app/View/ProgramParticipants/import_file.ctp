<div class="participants index">
	<div class="ttc-page-title">
		<h3><?php echo __("Import Participants"); ?></h3>
	   
	    <ul class="ttc-actions">
			<li><?php echo $this->Html->link(__('Add Participant'), array('program' => $programDetails['url'], 'controller' => 'programParticipants', 'action' => 'add'), array('class'=>'ttc-button')); ?></li>
			<li><?php echo $this->Html->link(__('View Participant(s)'), array('program' => $programDetails['url'], 'controller' => 'programParticipants', 'action' => 'index'), array('class'=>'ttc-button'));?></li>
		</ul>
    </div>
    <div class="ttc-display-area">
    <?php echo $this->element('participant_import_tabs', array('type' => 'file')); ?>
	</div>
</div>
