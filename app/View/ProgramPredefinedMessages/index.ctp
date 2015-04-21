<div class="predefined_messages index">
	<ul class="ttc-actions">
		<li><?php echo $this->Html->link(__('+ New Predefined Message'), array('program'=>$programDetails['url'], 'action' => 'add'), array('class' => 'ttc-button')); ?></li>
	</ul>	
	<h3><?php echo __('Predefined Messages');?></h3>
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
	<table class="predefined-messages" cellpadding="0" cellspacing="0">
	    <thead>
	        <tr>
			    <th class="name"><?php echo $this->Paginator->sort(__('name'), null, array('url'=> array('program' => $programDetails['url'])));?></th>
			    <th class="content"><?php echo $this->Paginator->sort(__('content'), null, array('url'=> array('program' => $programDetails['url'])));?></th>
			    <th class="actions action"><?php echo __('Actions');?></th>
			</tr>
		</thead>
		<tbody>
		    <?php foreach ($predefinedMessages as $predefinedMessage): ?>
		    <tr>
		        <td class="name"><?php echo h($predefinedMessage['PredefinedMessage']['name']); ?>&nbsp;</td>
		        <td class="content"><?php echo $predefinedMessage['PredefinedMessage']['content'] ?></td>
		        <td class="actions action">
		            <?php echo $this->Html->link(__('Edit'), array('program' => $programDetails['url'], 'controller' => 'programPredefinedMessages', 'action' => 'edit', $predefinedMessage['PredefinedMessage']['_id'])); ?>
		            <?php echo $this->Form->postLink(
		                __('Delete'),
		                array('program' => $programDetails['url'],
		                    'controller' => 'programPredefinedMessages',
		                    'action' => 'delete',
		                    $predefinedMessage['PredefinedMessage']['_id']),
		                null,
		                __('Are you sure you want to delete "%s"?', $predefinedMessage['PredefinedMessage']['name'])); ?>
		        </td>
		    </tr>
		   <?php endforeach; ?>
		 </tbody>
	</table>
	</div>
	</div>
</div>
