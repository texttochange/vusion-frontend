<div class='content_variables index'>
	<ul class="ttc-actions">
		<li><?php echo $this->Html->link(__('New Content Variable'), array('program'=>$programDetails['url'], 'action' => 'add'), array('class' => 'ttc-button')); ?></li>
	</ul>	
	<h3><?php echo __('Content Variables');?></h3>
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
			    <th class="direction"><?php echo $this->Paginator->sort(__('keys'), null, array('url'=> array('program' => $programDetails['url'])));?></th>
			    <th class="responses"><?php echo $this->Paginator->sort(__('value'), null, array('url'=> array('program' => $programDetails['url'])));?></th>
			    <th class="actions action"><?php echo __('Actions');?></th>
			</tr>
		</thead>
		<tbody>
		    <?php foreach ($contentVariables as $contentVariable): ?>
		    <tr>
		        <td class="prefix">
		            <?php
		                $keypair = '';
                        foreach ($contentVariable['ContentVariable']['keys'] as $key => $value) {
                            foreach ($value as $key1 => $value1) {
                                $keypair = $keypair . $value1 . ".";
                            }                            
                        }
                        $keypair = rtrim($keypair, '.');
		                echo h($keypair);
		            ?>&nbsp;
		        </td>
		        <td class="details"><?php echo __($contentVariable['ContentVariable']['value']) ?></td>
		        <td class="actions action">
		            <?php echo $this->Html->link(__('Edit'), array('program' => $programDetails['url'], 'controller' => 'programContentVariables', 'action' => 'edit', $contentVariable['ContentVariable']['_id'])); ?>
		            <?php echo $this->Form->postLink(
		                __('Delete'),
		                array('program' => $programDetails['url'],
		                    'controller' => 'programContentVariables',
		                    'action' => 'delete',
		                    $contentVariable['ContentVariable']['_id']),
		                null,
		                __('Are you sure you want to delete "%s"?', $keypair)); ?>
		        </td>
		    </tr>
		   <?php endforeach; ?>
		 </tbody>
	</table>
	</div>
	</div>
</div>
