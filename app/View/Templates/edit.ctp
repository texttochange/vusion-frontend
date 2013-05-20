<div class="templates form users-index program-body">
    <h3><?php echo __('Edit Template'); ?></h3>
    <?php echo $this->Form->create('Template'); ?>
       <?php echo $this->Form->input(__('name')); ?>
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
       <?php echo $this->Form->input(__('template'), array('rows'=>3)); ?>
       <?php $this->Js->get('document')->event('ready','addContentFormHelp();'); ?>
    <?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="admin-action">
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('View Templates'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index')); ?></li>
	</ul>	
</div>
</div>
