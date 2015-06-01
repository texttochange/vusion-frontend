<div class="predefined_messages index">
    <?php
       $contentTitle           = __('Predefined Messages'); 
       $contentActions         = array();
       $containsDataControlNav = true;
       $controller             = 'programPredefinedMessages';
       
       $contentActions[] = $this->Html->link(__('+ New Predefined Message'),
           array('program'=>$programDetails['url'],
               'controller' => $controller,
               'action' => 'add'),
           array('class' => 'ttc-button'));
       
       echo $this->element('header_content', compact('contentTitle', 'contentActions', 'containsDataControlNav', 'controller'));
    ?>
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
