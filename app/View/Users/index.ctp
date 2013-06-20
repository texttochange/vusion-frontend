<div class="users-index">
	<h3><?php echo __('Users');?></h3>
	<div class="ttc-data-control">
	<div id="data-control-nav" class="ttc-paging paging">
	<?php
	echo "<span class='ttc-page-count'>";
	echo $this->Paginator->counter(array(
	    'format' => __('{:start} - {:end} of {:count}')
	    ));
	echo "</span>";
	echo $this->Paginator->prev('<', array('url'=> array('program' => $programDetails['url'], '?' => $this->params['url'])), null, array('class' => 'prev disabled'));
	//echo $this->Paginator->numbers(array('separator' => ''));
	echo $this->Paginator->next(' >', array('url'=> array('program' => $programDetails['url'], '?' => $this->params['url'])), null, array('class' => 'next disabled'));
	?>
	</div>
	</div>
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
		<li><?php echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index')); ?></li>
	</ul>
</div>
</div>

