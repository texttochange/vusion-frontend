<div class="templates form">
    <h3><?php echo __('Add Template'); ?></h3>
    <?php echo $this->Form->create('Template'); ?>
       <?php echo $this->Form->input(__('name')); ?>
       <?php echo $this->Form->input(__('template'), array('rows'=>3)); ?>
       <?php $this->Js->get('document')->event('ready','addContentFormHelp();'); ?>
    <?php echo $this->Form->end(__('Submit')); ?>


</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('View Templates'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index')); ?></li>
	</ul>	
</div>
