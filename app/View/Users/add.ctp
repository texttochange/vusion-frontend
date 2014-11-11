<div class="users form users-index program-body">
<h3><?php echo __('Add User'); ?></h3>
<?php echo $this->Form->create('User');?>
	<fieldset>
		
	<?php
		echo $this->Form->input('username', array('label' => __('Username')));
		echo $this->Form->input('password', array('label' => __('Password')));
		echo $this->Form->input('email', array('label' => __('Email')));
		echo $this->Form->input('group_id', array('label' =>__('Group id')));
		$options = $programs;		
		echo $this->Form->input(
			'Program', array(
			    'options'=>$options,
			    'type'=>'select',
			    'multiple'=>true,
			    'label'=>__('Program'),	                
			    'style'=>'margin-bottom:0px'
				));
	    $this->Js->get('document')->event('ready','$("#ProgramProgram").chosen();');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="admin-action">
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Users'), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index')); ?></li>
	</ul>
</div>
</div>
