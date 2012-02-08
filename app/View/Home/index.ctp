<div>
	<h2><?php echo __('Home').' of '.$programName.' program';?></h2>
	<?php if (!$hasScriptActive && !$hasScriptDraft) { ?>
		<div class='ttc-info-box'>
	<?php
		echo $this->Html->tag('div', 
				'No script has been defined for this program',
				array('class' => 'ttc-text')
				);
		if ($isScriptEdit) {
			echo $this->Html->link('Create script', 
				array('program' => $programUrl,
				      'controller' => 'scripts'
				      ),
				array('class' => 'ttc-button')
				);
		}; ?>
		</div>
		<?php } else { ?>
	       
	       <?php
	       	  if ($hasScriptDraft) { ?>
	       	 <div class='ttc-info-box'>
	       	  <?php
			echo $this->Html->tag('div', 
				'A draft script has been defined for this program',
				array('class' => 'ttc-text')
				);
			if ($isScriptEdit) {	
			echo $this->Html->link('Edit draft', 
				array('program' => $programUrl,
				      'controller' => 'scripts',
				      'action' => 'draft'
				      ),
				array('class' => 'ttc-button')
				);
			echo $this->Html->link('Activate draft', 
				array('program' => $programUrl,
				      'controller' => 'scripts',
				      'action' => 'activate_draft'
				      ),
				array('class' => 'ttc-button')
				);
			/*echo $this->Html->tag('span', 'Activate draft',
				array('class' => 'ttc-button',
					'id'=> 'activate-button')
				);
			$this->Js->get('#activate-button')->event('click','$.get(
				"'.$programName.'/scripts/activate_draft"
				);', true);*/
			} ?> 
			</div>
			<?php
		  }; ?>
		  
		  <div class='ttc-info-box'>
		  <?php
		  if ($hasScriptActive) {
			echo $this->Html->tag('div', 
				'A script is already active for this program',
				array('class' => 'ttc-text')
				);
			if ($isScriptEdit) {
			echo $this->Html->link('Edit script', 
				array('program' => $programUrl,
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
				'Participants: '.$participantCount,
				array('class' => 'ttc-text')
				); ?>
	<?php if ($isParticipantAdd) { 
		echo $this->Html->link('Add participant(s)',
			array('program' => $programName,
				'controller' => 'participants',
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
	<div class='ttc-info-box'>
	<?php echo $this->Html->tag('div', 
				'Program History: '.$statusCount.' item(s)',
				array('class' => 'ttc-text')
				); ?>
	<?php if ($statusCount > 0) {
		echo $this->Html->link('View Program History',
			array('program' => $programName,
				'controller' => 'status', 
				),
			array('class' => 'ttc-button')
			);
		}?>
	</div>
	</div>
	
</div>
<?php echo $this->Js->writeBuffer(); ?>
