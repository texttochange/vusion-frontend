<div class="participants index width-size">
    <?php
        $contentTitle   = __('Import Participants'); 
        $contentActions = array();
        $controller     = 'programParticipants';
        
        $contentActions[] = $this->Html->link( __('Cancel'), 
        array(
          'program' => $programDetails['url'],
          'action' => 'index'),
        array('class' => 'ttc-button'));
        
        $contentActions[] = $this->Html->link(__('Add Participant'),
		    array('program' => $programDetails['url'],
		        'controller' => $controller,
		        'action' => 'add'),
		    array('class'=>'ttc-button'));
		
		$contentActions[] = $this->Html->link(__('View Participant(s)'),
		    array('program' => $programDetails['url'],
		        'controller' => $controller,
		        'action' => 'index'),
		    array('class'=>'ttc-button'));
		
		echo $this->element('header_content', compact('contentTitle', 'contentActions', 'controller'));
    ?>
    <div class="ttc-display-area display-height-size">
	<?php
	    echo $this->Form->create('Import', array('type' => 'file'));
		echo $this->Form->input('Import.file', array(
		    'between' => '<br />',
		    'type' => 'file'
		));
		echo $this->Form->input('tags', array('label' => __('Tag imported participants')));
		$options = array();
		if (isset($selectOptions)) {
		    $options  = $selectOptions;
		}
		echo $this->Form->input('enrolled', array(
		    'options'=>$options,
		    'type'=>'select',
		    'multiple'=>true,
		    'label'=>__('Enroll'),
		    'selected'=>' ',
		    'style'=>'margin-bottom:0px'
		    ));
		$this->Js->get('document')->event('ready','$("#ImportEnrolled").chosen();');
		
		$options = array(
		    null => __('keep'),     
		    'replace' => __('replace'),
		    'update' => __('update'));
		$attributes = array(
		    'legend' => false,
		    'id' => 'import-type',
		    'empty' => false);
		
		$importTypeSelectOptions =  $this->Form->select(
		    'import-type',
		    $options,
		    $attributes);	
		echo $this->Html->tag('div', __('If participant already exists '.$importTypeSelectOptions.' their current tags and labels.'), array('style'=>'margin-bottom:0px'));
		
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
