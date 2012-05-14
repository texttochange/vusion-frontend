<div class="templates form">
    <h3><?php echo __('Edit Template'); ?></h3>
    <?php echo $this->Form->create('Template'); ?>
       <?php echo $this->Form->input(__('name')); ?>
       <?php echo $this->Form->input(__('description'), array('rows'=>3)); ?>
       <?php echo $this->Form->input(__('translation'), array('rows'=>3)); ?>
    <?php echo $this->Form->end(__('Submit')); ?>

</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	
</div>
