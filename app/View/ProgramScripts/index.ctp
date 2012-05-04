<div>

<div class="Scripts index">
	<h3><?php echo __('Scripts Index');?></h3>
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
			      'controller' => 'programScripts',
			      'action' => 'draft'
			      ),
			array('class' => 'ttc-button')
			);
			}; 
		 ?>
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
				      'controller' => 'programScripts',
				      'action' => 'draft'
				      ),
				array('class' => 'ttc-button')
				);
			echo $this->Html->link('Activate draft', 
				array('program' => $programUrl,
				      'controller' => 'programScripts',
				      'action' => 'activateDraft'
				      ),
				array('class' => 'ttc-button')
				);
			/*echo $this->Html->tag('span', 'Activate draft',
				array('class' => 'ttc-button',
					'id'=> 'activate-button')
				);
			$this->Js->get('#activate-button')->event('click','$.get(
				"'.$programName.'/scripts/activateDraft"
				);', true);*/
			} ?> 
			</div>
			<?php
		  }; 
		  if ($hasScriptActive) {
		  ?>
		  
		  <div class='ttc-info-box'>
		  <?php
			echo $this->Html->tag('div', 
				'A script is already active for this program',
				array('class' => 'ttc-text')
				);
			if ($isScriptEdit) {
			echo $this->Html->link('Edit script', 
				array('program' => $programUrl,
				      'controller' => 'programScripts',
				      'action' => 'active'
				      ),
				array('class' => 'ttc-button')
				);
			}
			?>
			</div>
			<?php
			if (isset($workerStatus)) {
			?>
			<div class='ttc-info-box'>
			<?php 
			if ($workerStatus['running']) {
			     echo $this->Html->tag('div', 
			         'Vumi has a worker for this script', 
			         array('class'=>'ttc-text')
			         );
			} else {
			     echo $this->Html->tag('div', 
			         "WARNING Vumi DOESN'T have a worker for this script",
			         array('class'=>'ttc-text'));
			}
			?>
			</div>
			<?php
			};
		  }; 
	       } ?>
	<div class='ttc-info-box'>
	   <?php echo $this->Html->link(__('Script Simulator'),
	        array(
	            'program' => $programUrl,
                    'controller' => 'programSimulator', 
                    'action' => 'simulate'),
	        array('class' => 'ttc-button')
	        );
	        ?>
	</div>	
</div>
<?php echo $this->Js->writeBuffer(); ?>
