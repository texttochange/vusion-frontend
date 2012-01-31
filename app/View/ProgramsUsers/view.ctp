<div class="programsUsers view">
<h2><?php  echo __('Programs User');?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($programsUser['ProgramsUser']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Program'); ?></dt>
		<dd>
			<?php echo $this->Html->link($programsUser['Program']['name'], array('controller' => 'programs', 'action' => 'view', $programsUser['Program']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('User'); ?></dt>
		<dd>
			<?php echo $this->Html->link($programsUser['User']['username'], array('controller' => 'users', 'action' => 'view', $programsUser['User']['id'])); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Programs User'), array('action' => 'edit', $programsUser['ProgramsUser']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Programs User'), array('action' => 'delete', $programsUser['ProgramsUser']['id']), null, __('Are you sure you want to delete # %s?', $programsUser['ProgramsUser']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Programs Users'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Programs User'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Programs'), array('controller' => 'programs', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Program'), array('controller' => 'programs', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
