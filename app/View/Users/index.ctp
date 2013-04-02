<div class="users index">
	<h3><?php echo __('Users');?></h3>
	<div id="data-control-nav" class="ttc-paging paging">
	<?php
	echo "<span class='ttc-page-count'>";
	echo $this->Paginator->counter(array(
	    'format' => __('{:start} - {:end} of {:count}')
	    ));
	echo "</span>";
	echo $this->Paginator->prev('<', array('url'=> array('program' => $programUrl, '?' => $this->params['url'])), null, array('class' => 'prev disabled'));
	//echo $this->Paginator->numbers(array('separator' => ''));
	echo $this->Paginator->next(' >', array('url'=> array('program' => $programUrl, '?' => $this->params['url'])), null, array('class' => 'next disabled'));
	?>
	</div>
	<div class="ttc-table-display-area">
	<div class="ttc-table-scrolling-area">
	<table cellpadding="0" cellspacing="0">
	    <thead>
	        <tr>
			    <th id="content-css"><?php echo $this->Paginator->sort('username');?></th>
			    <th id="content-css"><?php echo $this->Paginator->sort('group_id');?></th>
			    <th class="actions" id="action-admin-css"><?php echo __('Actions');?></th>
			 </tr>
	    </thead>
	    <tbody>
	        <?php foreach ($users as $user): ?>
	        <tr>
	            <td id="content-css"><?php echo h($user['User']['username']); ?></td>
	            <td id="content-css"><?php echo h($user['Group']['name']); ?></td>
	            <td class="actions" id="action-admin-css">
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
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New User'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index')); ?></li>
	</ul>
</div>
