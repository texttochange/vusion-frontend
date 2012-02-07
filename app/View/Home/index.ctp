<div>
	<h2><?php echo __('Home').' of '.$programName.' program';?></h2>
	<div class='ttc-info-box'>
	<?php if (!$hasScriptActive && !$hasScriptDraft) {
		echo $this->Html->tag('div', 
				'No script has been defined for this program',
				array('class' => 'ttc-text')
				);
		if ($isScriptEdit) {
			echo $this->Html->link('Create script', 
				array('program' => $programName,
				      'controller' => 'scripts'
				      ),
				array('class' => 'ttc-button')
				);
		};
	       } else {
	       	  if ($hasScriptDraft) {
			echo $this->Html->tag('div', 
				'A draft script has been defined for this program',
				array('class' => 'ttc-text')
				);
			if ($isScriptEdit) {	
			echo $this->Html->link('Edit draft', 
				array('program' => $programName,
				      'controller' => 'scripts',
				      'action' => 'draft'
				      ),
				array('class' => 'ttc-button')
				);
			}
		  };
		  if ($hasScriptActive) {
			echo $this->Html->tag('div', 
				'A script is already active for this program',
				array('class' => 'ttc-text')
				);
			if ($isScriptEdit) {
			echo $this->Html->link('Edit script', 
				array('program' => $programName,
				      'controller' => 'scripts',
				      'action' => 'active'
				      ),
				array('class' => 'ttc-button')
				);
			}
		  }; 
	       } ?>
	</div>
	<div class='ttc-info-box'>
	<?php echo $this->Html->tag('div', 
				'Number of participants: '.$participantCount,
				array('class' => 'ttc-text')
				); ?>
	<?php if ($isParticipantAdd) { 
		echo $this->Html->link('Add participant(s)',
			array('program' => $programName,
				'controller' => 'Participants',
				'action' => 'add'
				),
			array('class' => 'ttc-button')
			);
		}?>
	<?php if ($participantCount > 0) {
		echo $this->Html->link('View participant(s)',
			array('program' => $programName,
				'controller' => 'Participants', 
				),
			array('class' => 'ttc-button')
			);
		}?>
	</div>
	
</div>
<?php echo $this->Js->writeBuffer(); ?>
