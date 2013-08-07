<div class="groups index users-index">
	<h3><?php echo __('Groups');?></h3>
	<div class="ttc-data-control">
	<div id="data-control-nav" class="ttc-paging paging">
	<?php
	echo "<span class='ttc-page-count'>";
	echo $this->Paginator->counter(array(
	    'format' => __('{:start} - {:end} of {:count}')
	    ));
	echo "</span>";
	echo $this->Paginator->prev('<', array(), null, array('class' => 'prev disabled'));
	//echo $this->Paginator->numbers(array('separator' => ''));
	echo $this->Paginator->next(' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
	</div>
	<div class="ttc-table-display-area">
	<div class="ttc-table-scrolling-area display-height-size">
	<table cellpadding="0" cellspacing="0">
	    <thead>
	        <tr>
			    <th class="content"><?php echo $this->Paginator->sort('name');?></th>
			    <th class="content"><?php echo $this->Paginator->sort('specific_program_access');?></th>
			    <th class="action-admin"><?php echo __('Actions');?></th>
			</tr>
	    </thead>
	    <tbody>
	        <?php foreach ($groups as $group): ?>
	        <tr>
	            <td class="content"><?php echo h($group['Group']['name']); ?>&nbsp;</td>
	            <td class="content"><?php echo h($group['Group']['specific_program_access']); ?>&nbsp;</td>
	            <td class="actions action-admin">
	                <?php echo $this->Html->link(__('View'), array('action' => 'view', $group['Group']['id'])); ?>
	                <?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $group['Group']['id'])); ?>
	                <?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $group['Group']['id']), null, __('Are you sure you want to delete "%s" group?', $group['Group']['name'])); ?>
	             </td>
	         </tr>
	        <?php endforeach; ?>
	      </tbody>
	</table>
	</div>
	</div>	
  </div>
    <div class="admin-action">
    <div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Group'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index')); ?></li>
	</ul>
</div>
</div>
