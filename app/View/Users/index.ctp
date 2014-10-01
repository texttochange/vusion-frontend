<div class="users-index">
    <ul class="ttc-actions">
        <li><?php
		echo $this->Html->tag(
		    'span', 
		    __('Filter'), 
		    array('class' => 'ttc-button', 'name' => 'add-filter')); 
		$this->Js->get('[name=add-filter]')->event(
		    'click',
		    '$("#advanced_filter_form").show();
		    createFilter();
		    addStackFilter();');
		?></li>
    </ul>
	<h3><?php echo __('Users');?></h3>
	<?php	
    echo $this->element('filter_box', array(
        'controller' => 'users'));
	?>
	<div class="ttc-table-display-area">
	<div class="ttc-table-scrolling-area display-height-size">
	<table cellpadding="0" cellspacing="0">
	    <thead>
	        <tr>
			    <th class="content"><?php echo $this->Paginator->sort('username');?></th>
			    <th class="content"><?php echo $this->Paginator->sort('group_id');?></th>
			    <th class="action-admin"><?php echo __('Actions');?></th>
			 </tr>
	    </thead>
	    <tbody>
	        <?php foreach ($users as $user): ?>
	        <tr>
	            <td class="content"><?php echo h($user['User']['username']); ?></td>
	            <td class="content"><?php echo h($user['Group']['name']); ?></td>
	            <td class="action-admin actions">
	                <?php echo $this->Html->link(__('View'), array('action' => 'view', $user['User']['id'])); ?>
	                <?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $user['User']['id'])); ?>
	                <?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $user['User']['id']), null, __('Are you sure you want to delete the user "%s" ?', $user['User']['username'])); ?>
	             </td>
	             </tr>
	          <?php endforeach; ?>
	     <tbody>
	</table>
	</div>
	</div>
	</div> 
	<div class="admin-action">
	<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New User'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('User Logs'), array('controller' => 'userLogs', 'action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index')); ?></li>
	</ul>
</div>
</div>	
