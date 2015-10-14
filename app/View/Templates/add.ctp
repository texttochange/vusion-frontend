<?php
    $this->RequireJs->scripts(array("jquery", "ttc-utils"));
?>
<div class="admin-action">
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Templates List'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index')); ?></li>
	</ul>	
</div>
</div>

<div class="templates form admin-index">
<div class="table">
<div class="row">
<div class="cell">
    <h3><?php echo __('Add Template'); ?></h3>
    <?php echo $this->Form->create('Template'); ?>
    <fieldset>
       <?php echo $this->Form->input('name', array('label' => __('Name'))); ?>
       <div class="input select required <?php if ($this->Form->isFieldError('type-template')) {echo "error";}?>">
       <?php 
       echo $this->Form->label(__('Template type'));
       echo "<br>";
       echo $this->Form->select('type-template', $typeTemplateOptions, array(
           'empty'=> __('Template type...'))); 
       if ($this->Form->isFieldError('type-template')) {
           echo $this->Form->error('type-template');
       }
       ?>
       </div>
       <?php echo $this->Form->input('template', array('rows'=>3, 'label' => __('template'))); ?>
       <?php $this->RequireJs->runLine('addContentFormHelp();'); ?>
   </fieldset>
       <?php echo $this->Form->end(__('Submit')); ?>
</div>
</div>
</div>
</div>
