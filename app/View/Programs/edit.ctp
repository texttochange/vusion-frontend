<div class="programs form program-edit">
<h3><?php echo __('Admin of %s', $this->data['Program']['name']); ?></h3>

<fieldset>
<h3><?php echo __('Edit'); ?></h3>
<?php echo $this->Form->create('Program');?>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('name', array('label' => 'Name'));		
		echo $this->Form->input('url');
		echo $this->Form->input('database',
		    array('label' => 'Database',
		        'readonly' => 'true',
		        'style' => 'color:#AAAAAA'));
	?>
<?php echo $this->Form->end(__('Save'));?>
</fieldset>
</div>
<div class="admin-action">
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul> 
		<li><?php
		echo $this->Form->postLink(__('Archive Program'), array('action' => 'archive', $this->data['Program']['id']), array('name'=>'archive-program'), __('Are you sure you want to archive %s? Archiving this program will stop any sending or receiving of SMS and will free any keyword(s) used on the shortcode. Still you will be able to access program data.', $this->data['Program']['name'])); 
		?></li>
		<li><?php 
		echo $this->Form->postLink(__('Delete Program'), array('action' => 'delete', $this->data['Program']['id']), array('name'=>'delete-program'), __('Are you sure you want to delete %s? Deleting this program will stop all sending or receiving of SMS, will release any keyword(s) used and will delete ALL DATA. Still the credit consumed will be the only data left.', $this->data['Program']['name']));
		?></li>
		<li><?php 
		echo $this->Html->link(__('List Programs'), array('action' => 'index'));
		?></li>
		<li><?php 
		echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index'));
		?></li>
	</ul>
</div>
</div>
