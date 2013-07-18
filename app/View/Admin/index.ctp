<div class="users-index program-body">
	<h3><?php echo __('Admin');?></h3>
	<p>Page under construction</p>
	<p>Any suggestions are welcome.</p>	
</div>
<div class="admin-action">
<div class="actions break">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Users Management'), array('controller' => 'users','class'=>'p')); ?></li>
		<li><?php echo $this->Html->link(__('Groups Management'), array('controller' => 'groups')); ?> </li>
		<li><?php echo $this->Html->link(__('Programs Management'), array('controller' => 'programs')); ?> </li>
		<li><?php echo $this->Html->link(__('Shortcodes Management'), array('controller' => 'shortCodes')); ?> </li>
		<li><?php echo $this->Html->link(__('Templates Management'), array('controller' => 'templates')); ?> </li>
	</ul>
</div>
</div>



