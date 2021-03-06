<?php
  $this->RequireJs->scripts(array("chosen"));
?>
<div class='table tabs' style='width:100%; margin-top:10px'>
<div class='row' style='width:100%'>
<span class='cell'>
<ul>
<li <?php echo ($type === 'file' ? 'class="selected"' : ''); ?> >
    <a href="<?php echo $this->Html->url(array('program' => $programDetails['url'], 'action' => 'importFile')) ?>" >
        <label><?php echo __("From File") ?></label>
    </a>
</li>
<li <?php echo ($type === 'mash' ? 'class="selected"' : ''); ?> >
    <a href="<?php echo $this->Html->url(array('program' => $programDetails['url'], 'action' => 'importMash')) ?>" >
        <label><?php echo __("From Mash") ?></label>
    </a>
</li>
</ul>
</span>
</div>
</div>
<?php
switch ($type) {
case 'file': 
	echo $this->Form->create('Import', array('type' => 'file'));
	break;
case 'mash':
	echo $this->Form->create('Import');
	break;
}
?>
<div class="tab-content">
<?php
switch ($type) {
case 'file':
	echo $this->Form->input('Import.file', array(
	    'type' => 'file',
	    'label' => false
	));
	break;
case 'mash':
	echo $this->Html->tag(
		'div', 
		__('Import all participants who are matching the following filter:'),
		array('style' => 'margin-bottom:0px'));
	echo '<div style="margin-left:10px">';
	echo $this->Html->tag('label', _('from'));
  if (count($importCountries) == 1) {
  	echo $this->Form->select('country', 
  		$importCountries,
  		array('value' => key($importCountries)));
  } else {
    $importCountries['none'] = __('select');
    echo $this->Form->select('country', 
      $importCountries,
      array('value' => 'none'));
  }
	echo '</div>';
	break;
}
?>
</div>
<?php
echo '<div>';
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
$this->RequireJs->runLine('$("#ImportEnrolled").chosen();');

$options = array(
    'keep' => __('keep'),     
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
echo $this->Html->tag(
  'div', 
  __('If participant already exists, %s their current tags and labels.', $importTypeSelectOptions),
  array('style'=>'margin-bottom:0px'));

echo $this->Form->end(__('Import'));
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