<div class="admin-action">
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Program'), array('action' => 'edit', $program['Program']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Program'), array('action' => 'delete', $program['Program']['id']), null, __('Are you sure you want to delete program "%s" ?', $program['Program']['name'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Programs'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Program'), array('action' => 'add')); ?> </li>
	</ul>
</div>
</div>
<div class="programs view users-index program-body">
<h2><?php  echo __('Program');?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($program['Program']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($program['Program']['name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Url'); ?></dt>
		<dd>
			<?php echo h($program['Program']['url']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Database'); ?></dt>
		<dd>
			<?php echo h($program['Program']['database']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($program['Program']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($program['Program']['modified']); ?>
			&nbsp;
		</dd>
	</dl>
</div>

