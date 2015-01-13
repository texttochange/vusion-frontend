<div class="users form users-index program-body">
<h3><?php echo __('Edit User'); ?></h3>
<?php echo $this->Form->create('User', array('type' => 'post'));?>
	<fieldset>
		
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('username', array('label' => __('Username')));
		echo "<div>";
		echo $this->Html->link(__('Change Password'), array('action' => 'changePassword', $this->Form->value('User.id')));
		echo "</div>";
		echo $this->Form->input('email', array('label' => __('Email')));
		$isAdmin = $this->AclLink->_allow('controllers/Admin');
		if ($isAdmin) {
		    echo $this->Form->input('group_id', array('label' => __('Group id')));
		    $options = $programs;		
		    echo $this->Form->input('Program', array('options'=>$options,
		        'type'=>'select',
		        'multiple'=>true,
		        'label'=> __('Program'),	                
		        'style'=>'margin-bottom:0px'
		        ));
		    $this->Js->get('document')->event('ready','$("#ProgramProgram").chosen();');
            echo $this->Form->checkbox('unmatchable_reply_access');
            echo $this->Html->tag('label',__('Access Unmatchable Replies'));
            echo "<br /><br />";
            echo $this->Form->checkbox('can_invite_users');
            echo $this->Html->tag('label',__('Invite new users'));		    
		}
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="admin-action">
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php
		if ($isAdmin) {
		    echo $this->Form->postLink(__('Delete User'), array('action' => 'delete', $this->Form->value('User.id')), null, __('Are you sure you want to delete the user "%s" ?', $this->Form->value('User.username'))); 
		}
		?>
		</li>
		<li><?php
		if ($isAdmin) {
		    echo $this->Html->link(__('List Users'), array('action' => 'index'));
		}
		?>
		</li>
		<li><?php 
		if ($isAdmin) {
		echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index'));
		}else{
		echo $this->Html->link(__('Back to Programs'), array('controller' => 'programs', 'action' => 'index')); 
		}
		?></li>
	</ul>
</div>
</div>
