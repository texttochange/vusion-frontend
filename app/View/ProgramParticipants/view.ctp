<div class="participant view">
<h3>Participant</h3>
	<dl>
		<dt><?php echo __('phone'); ?></dt>
		<dd>
			<?php echo h($participant['Participant']['phone']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('name'); ?></dt>
		<dd>
			<?php echo h($participant['Participant']['name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('history'); ?></dt>
		<dd>
			<table cellpadding="0" cellspacing="0">
			<tr>
					<th><?php echo __('message');?></th>
					<th><?php echo __('time');?></th>
			</tr>
			<?php
			foreach ($histories as $history): ?>
			
			<tr>
				<?php if (isset($history['History']['content'])) { ?>
				<td><?php echo h($history['History']['content']); ?>&nbsp;</td>
				<?php } else { ?>
				<td><?php echo h($history['History']['message-content']); ?>&nbsp;</td>
				<?php } ?>
				<td><?php echo h($history['History']['timestamp']); ?>&nbsp;</td>
			</tr>
			<?php endforeach; ?>
			</table>
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Participant'), array('action' => 'edit', $participant['Participant']['_id'])); ?> </li>
	</ul>
</div>

