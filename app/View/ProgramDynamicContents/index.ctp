<div class='dynamic_contents index'>
	<ul class="ttc-actions">
		<li><?php echo $this->Html->link(__('New Dynamic Content'), array('program'=>$programDetails['url'], 'action' => 'add'), array('class' => 'ttc-button')); ?></li>
	</ul>	
	<h3><?php echo __('Dynamic Contents');?></h3>
  <div class="ttc-data-control">
	<div id="data-control-nav" class="ttc-paging paging">
	<?php
	echo "<span class='ttc-page-count'>";
	echo $this->Paginator->counter(array(
	    'format' => __('{:start} - {:end} of {:count}')
	    ));
	echo "</span>";
	echo $this->Paginator->prev('<', array('url'=> array('program' => $programDetails['url'], '?' => $this->params['url'])), null, array('class' => 'prev disabled'));
	echo $this->Paginator->next(' >', array('url'=> array('program' => $programDetails['url'], '?' => $this->params['url'])), null, array('class' => 'next disabled'));
	?>
	</div>
  </div>
	<div class="ttc-table-display-area">
	<div class="ttc-table-scrolling-area display-height-size">
	<table cellpadding="0" cellspacing="0">
	    <thead>
	        <tr>
			    <th class="direction"><?php echo $this->Paginator->sort(__('key'), null, array('url'=> array('program' => $programDetails['url'])));?></th>
			    <th class="responses"><?php echo $this->Paginator->sort(__('value'), null, array('url'=> array('program' => $programDetails['url'])));?></th>
			    <th class="actions action"><?php echo __('Actions');?></th>
			</tr>
		</thead>
		<tbody>
		    <?php foreach ($dynamicContents as $dynamicContent): ?>
		    <tr>
		        <td class="prefix"><?php echo h($dynamicContent['DynamicContent']['key']); ?>&nbsp;</td>
		        <td class="details"><?php echo __($dynamicContent['DynamicContent']['value']) ?></td>
		        <td class="actions action">
		            <?php echo $this->Html->link(__('Edit'), array('program' => $programDetails['url'], 'controller' => 'programDynamicContents', 'action' => 'edit', $dynamicContent['DynamicContent']['_id'])); ?>
		            <?php echo $this->Form->postLink(
		                __('Delete'),
		                array('program' => $programDetails['url'],
		                    'controller' => 'programDynamicContents',
		                    'action' => 'delete',
		                    $dynamicContent['DynamicContent']['_id']),
		                null,
		                __('Are you sure you want to delete "%s"?', $dynamicContent['DynamicContent']['key'])); ?>
		        </td>
		    </tr>
		   <?php endforeach; ?>
		 </tbody>
	</table>
	</div>
	</div>
</div>
