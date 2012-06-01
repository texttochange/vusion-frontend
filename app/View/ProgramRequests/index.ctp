<div class='Program Requests index'>
	<ul class="ttc-actions">
		<li><?php echo $this->Html->link(__('New Request'), array('program'=>$programUrl, 'action' => 'add')); ?></li>
	</ul>	
	<h3><?php echo __('Requests');?></h3>
	<div class='ttc-display-area'>
	<table cellpadding="0" cellspacing="0">
		<tr>
			<th><?php echo $this->Paginator->sort('keyword', null, array('url'=> array('program' => $programUrl)));?></th>
			<th><?php echo $this->Paginator->sort('responses', null, array('url'=> array('program' => $programUrl)));?></th>
			<th><?php echo $this->Paginator->sort('do', null, array('url'=> array('program' => $programUrl)));?></th>
			<th><?php echo __('Actions');?></th>
		</tr>
	<?php
	foreach ($requests as $request): ?>
	<tr>
		<td><?php echo $request['Request']['keyword']; ?>&nbsp;</td>
		<td>
		<?php 
		if (isset($request['Request']['responses']))
		    foreach ($request['Request']['responses'] as $response) {
		        echo $this->Html->tag('div', '"'.$response['content'].'"');
		    } 
		?>
		</td>
		<td>
		<?php 
		if (isset($request['Request']['actions']))
		    foreach ($request['Request']['actions'] as $action) {
		        $info = __($action['type-action']);
		        if ($action['type-action']=='enrolling')
		            foreach ($dialogues as $dialogue) {
		                if ($dialogue['dialogue-id'] == $action['enroll']) {
		                    $info = __("Enrolling dialogue %s", $dialogue['Active']['name']);
		                    break;
		                }
		            }
		        echo $this->Html->tag('div', $info);
		    } 
		?>
		</td>
		<td class="actions">
		    <?php echo $this->Html->link(__('Edit'), array('program'=>$programUrl, 'action' => 'edit', $request['Request']['_id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('program'=>$programUrl, 'action' => 'delete', $request['Request']['_id']), null,
			                                __('Are you sure you want to delete %s?', $request['Request']['keyword'])); ?>
		</td>
	</tr>
	<?php endforeach; ?>
	</table>
	</div>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>
	</p>
	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array('url'=> array('program' => $programUrl, '?'=>$this->params['url'])), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => '', 'url'=> array('program' => $programUrl, '?'=>$this->params['url'])));
		echo $this->Paginator->next(__('next') . ' >', array('url'=> array('program' => $programUrl, '?'=>$this->params['url'])), null, array('class' => 'next disabled'));
	?>
    </div>
</div>
	
<?php echo $this->Js->writeBuffer(); ?>
