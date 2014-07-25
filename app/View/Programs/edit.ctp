<div class="programs form program-edit width-size">
<h3><?php echo __('Admin', $this->data['Program']['name']); ?></h3>

<fieldset>
<div class="boxed-group">
	<h3><?php echo __('Admin Settings'); ?></h3>
<div class="boxed-group-inner">
<?php echo $this->Form->create('Program');?>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('name', array('label' => __('Name')));		
		echo $this->Form->input('url', array('label' => __('Url')));
		echo $this->Form->input(
			'database',
		    array(
		    	'label' => __('Database'),
		        'readonly' => 'true',
		        'style' => 'color:#AAAAAA'));
	?>
<?php echo $this->Form->end(__('Save'));?>
</div>
</div>
<div class="boxed-group">
	<h3 class="danger-zone"><?php echo __("Danger Zone"); ?></h3>
<div class="boxed-group-inner">
	<?php if ($this->data['Program']['status'] === 'running') { ?>
		<h4><?php echo __("Archive this program")?></h4>
		<?php
		echo $this->Form->postLink(
					__('Archive'), 
					array(
						'action' => 'archive', 
						$this->data['Program']['id']), 
					array(
						'class' => 'ttc-button danger',
						'style' => 'float:right'), 
					__('Are you sure you want to Archive %s? This action will stop any sending'
						.' or receiving of SMS and will free the keywords on the shortcode.'
						.' Still you will be able to access this program pages.', $this->data['Program']['name']));
		?>	
		<p> <?php echo __('Archiving a program will stop any sending and receiving'
						.' of SMS and will free the keywords on the shortcode.'
						.' Still you will be able to access this program pages.') ?></p>
    <?php } else { ?>
	    <h4><?php echo __("Unarchive this program")?></h4>
		<?php
		echo $this->Form->postLink(
					__('Unarchive'), 
					array(
						'action' => 'unarchive', 
						$this->data['Program']['id']), 
					array(
						'class' => 'ttc-button danger',
						'style' => 'float:right'), 
					__('Are you sure you want to Unarchive %s?'
						.' Still you will have to edit the program settings in order to select a shortcode.', $this->data['Program']['name']));
		?>	
		<p> <?php echo __('Unarchiving a program will resume its ability to send and receive'
						.' SMS. However the shortcode will be rest in the program setting,'
						.' Therefor one need to re-select a shortcode in this program setting.') ?></p>
    <?php } ?>
	<div class="rule"></div>
	<h4><?php echo __("Delete this program")?></h4>
	<?php
	echo $this->Form->postLink(
				__('Delete'), 
				array(
					'action' => 'delete', 
					$this->data['Program']['id']), 
				array(
					'class' => 'ttc-button danger',
					'style' => 'float:right'), 
					__('Are you sure you want to delete %s? Deleting this program will'
					.' stop all sending or receiving of SMS, will release any keyword(s) used'
					.' and will delete ALL DATA. Still the credit consumed will be the only'
					.' data left', $this->data['Program']['name']));
	?>
	<p><?php echo __('Delete this program stop the program and delete all data.'
					.' except the credit consumed data.')?></p>
</div>
</div>
</fieldset>
</div>
<div class="admin-action">
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul> 
		<li><?php 
		echo $this->Html->link(__('List Programs'), array('action' => 'index'));
		?></li>
		<li><?php 
		echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index'));
		?></li>
	</ul>
</div>
</div>
