<div class='content_variables index'>
	<ul class="ttc-actions">
		<li><?php echo $this->Html->link(__('New Keys/Value'), array('program'=>$programDetails['url'], 'action' => 'add'), array('class' => 'ttc-button')); ?></li>
	</ul>	
	<h3><?php echo __('Content Variables');?></h3>
	<div class="tabs">
	    <ul>
        <li><a href="<?php echo $this->Html->url(array('program' => $programDetails['url'], 'action' => 'index')) ?>" ><label><?php echo __("Keys/Values") ?></label></a></li>
        <li class="selected"><label><?php echo __("Tables") ?></label></li>
        </ul>
    </div>
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
	<table class="content-variable-tables" cellpadding="0" cellspacing="0">
	    <thead>
	        <tr>
			    <th class="name"><?php echo $this->Paginator->sort('name', __('Name'), array('url'=> array('program' => $programDetails['url'])));?></th>
			    <th class="values"><?php echo __('Values')?></th>
			    <th class="actions action"><?php echo __('Actions');?></th>
			</tr>
		</thead>
		<tbody>
		    <?php foreach ($contentVariableTables as $contentVariableTable): ?>
		    <tr>
		        <td><?php echo $contentVariableTable['ContentVariableTable']['name'] ?></td>
		        <td ><?php echo $contentVariableTable['ContentVariableTable']['value'] ?></td>
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
