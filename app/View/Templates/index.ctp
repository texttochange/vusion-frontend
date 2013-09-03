<div class="users-index">
	<h3><?php echo __('Templates');?></h3>
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
			    <th class="prefix"><?php echo $this->Paginator->sort('name');?></th>
			    <th class="prefix"><?php echo $this->Paginator->sort('type-template', 'Type');?></th>
			    <th class="details"><?php echo $this->Paginator->sort('template');?></th>
			    <th class="actions action"><?php echo __('Actions');?></th>
			</tr>
		</thead>
		<tbody>
		    <?php foreach ($templates as $template): ?>
		    <tr>
		        <td class="prefix"><?php echo h($template['Template']['name']); ?>&nbsp;</td>
		        <td class="prefix"><?php echo __($template['Template']['type-template']) ?></td>
		        <td class="details"><?php echo __($template['Template']['template']) ?></td>
		        <td class="actions action">
		            <?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $template['Template']['_id'])); ?>
		            <?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $template['Template']['_id']), null, __('Are you sure you want to delete "%s"?', $template['Template']['name'])); ?>
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
		<li><?php echo $this->Html->link(__('New Template'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index')); ?></li>
	</ul>
</div>
</div>
