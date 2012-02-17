<div class="programs view">
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
		<dt><?php echo __('Country'); ?></dt>
		<dd>
			<?php echo h($program['Program']['country']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Timezone'); ?></dt>
		<dd>
			<?php echo h($program['Program']['timezone']); ?>
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
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Program'), array('action' => 'edit', $program['Program']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Program'), array('action' => 'delete', $program['Program']['id']), null, __('Are you sure you want to delete # %s?', $program['Program']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Programs'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Program'), array('action' => 'add')); ?> </li>
	</ul>
</div>
