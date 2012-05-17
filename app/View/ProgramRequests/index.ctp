<div>
  <div class='Program Requests index'>
	<div class='ttc-info'>
	<h3><?php echo __('Requests');?></h3>
	<table cellpadding="0" cellspacing="0">
		<tr>
			<th><?php echo __('Keyword');?></th>
			<th><?php echo __('Responses');?></th>
			<th><?php echo __('Do');?></th>
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
		        echo $this->Html->tag('div', $action['type-action']);
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
	
  </div>
	
<?php echo $this->Js->writeBuffer(); ?>
