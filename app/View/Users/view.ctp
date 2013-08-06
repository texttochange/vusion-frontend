<div class="users view users-index program-body">
   <h3><?php  echo __('User');?></h3>
	<dl>
		<dt><?php echo __('Username'); ?></dt>
		<dd>
			<?php echo h($user['User']['username']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Password'); ?></dt>
		<dd>
			<?php echo $this->Html->link(__('Change Password'), array('action' => 'changePassword', $user['User']['id'])); ?>
		</dd>
		<dt><?php echo __('Email'); ?></dt>
		<dd>
			<?php echo h($user['User']['email']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Group'); ?></dt>
		<dd>
			<?php
			$isAdmin = $this->AclLink->_allow('controllers/Admin');
			if ($isAdmin) {
			echo $this->Html->link($user['Group']['name'], array('controller' => 'groups', 'action' => 'view', $user['Group']['id'])); 
			}else{
			    echo h($user['Group']['name']);
			}
			?>
			&nbsp;
		</dd>
		
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($user['User']['created']); ?>
			&nbsp;
		</dd>		
	</dl>
  <div class="related">
	<h3><?php echo __('Accessible Programs');?></h3>
	<?php if (!empty($user['Program'])):?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>l
		<th><?php echo __('Name'); ?></th>
		<th class="actions"><?php
		if ($isAdmin) {
		    echo __('Actions');
		}
		?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($user['Program'] as $program): ?>
		<tr>
		<td><?php echo $program['name'];?></td>			 
			<td class="actions">
			<?php
			if ($isAdmin) {
				echo $this->Html->link(__('View'), array('controller' => 'programs', 'action' => 'view', $program['id']));				
				echo $this->Html->link(__('Edit'), array('controller' => 'programs', 'action' => 'edit', $program['id'])); 
			}
			?>
			</td> 		
		</tr>
	<?php endforeach; ?>
	</table>
	<?php endif; ?>
  </div>
</div>
<div class="admin-action">
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit User'), array('action' => 'edit', $user['User']['id'])); ?> 
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
