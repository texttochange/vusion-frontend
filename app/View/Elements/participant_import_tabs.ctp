<span class="tabs">
<ul>
<li <?php echo ($type === 'file' ? 'class="selected"' : ''); ?> >
    <a href="<?php echo $this->Html->url(array('program' => $programDetails['url'], 'action' => 'importFile')) ?>" >
        <label><?php echo __("from File") ?></label>
    </a>
</li>
<li <?php echo ($type === 'mash' ? 'class="selected"' : ''); ?> >
    <a href="<?php echo $this->Html->url(array('program' => $programDetails['url'], 'action' => 'importMash')) ?>" >
        <label><?php echo __("from Mash") ?></label>
    </a>
</li>
</ul>
</span>
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
	    'between' => '<br />',
	    'type' => 'file',
	    'label' => false
	));
	break;
case 'mash':
	echo $this->Html->tag(
		'div', 
		__('Import all participants who are matching the following filters:'),
		array('style' => 'margin-bottom:0px'));
	echo '<div style="margin-left:10px">';
	echo $this->Html->tag('label', _('from'));
	echo $this->Form->select('country', 
		array('bolivia' => 'Bolivia'),
		array('disabled' => true,
			'value' => 'bolivia'));
	echo '</div>';
	break;
}
?>
</div>
<?php
echo $this->Form->input('tags', array('label' => __('Tag imported participants')));
echo '<div>';
echo $this->Form->checkbox('replace-tags-and-labels', array(
    'label' => 'Update participant',
    'value' => 'update',
    'hiddenField' => false));
echo $this->Html->tag('label',  _("If participant already in replace their tags and labels."));
echo '</div>';
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