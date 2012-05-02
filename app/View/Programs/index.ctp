<div class="ttc-program-index">
    <?php echo $this->Html->link(__('Create Program'), array('action' => 'add'), array('class' => 'ttc-button', 'style'=>'float:right')); ?>    
	<h3><?php echo __('Programs');?></h3>
	<?php
	foreach ($programs as $program): ?>
	<div class='ttc-program-box' onclick="window.location.pathname='<?php echo '/'.$program['Program']['url']; ?>'">
		<?php echo $this->Html->tag('div', $program['Program']['name'], array('class'=>'ttc-program-title')); ?>
		<?php
		if (isset($program['Program']['shortcode']))
		    echo $this->Html->tag('div', $program['Program']['shortcode'], array('class'=>'ttc-program-details')); ?>
		<?php
		    echo $this->Html->tag(
		        'div',
		        $program['Program']['participant-count'].__(' participant(s)').'<br/>'. $program['Program']['history-count'].__(' history(s)').'<br/>'. $program['Program']['schedule-count'].__(' schedule(s)'),
		        array('class'=>'ttc-program-stats')
		        );
		?>
		<?php if ($isProgramEdit) { ?>
		<div class="ttc-program-quicklinks">
			<?php echo $this->Html->link(__('System Settings'), array('action' => 'edit', $program['Program']['id'])); ?>
			<?php echo $this->Form->postLink(__('Archive'), array('action' => 'delete', $program['Program']['id']), null, __('Are you sure you want to archive # %s?', $program['Program']['name'])); ?>
		</div>
		<?php }; ?>
	</div>
    <?php endforeach; ?>
	</div>
<div class="ttc-program-news">
	<h3><?php echo __('Recent Issues'); ?></h3>
	<div class="ttc-program-box" onclick="window.location.pathname='<?php echo $this->Html->url(array('controller'=>'unmatchableReply','action' => 'index')) ?>'">
	<?php 
	echo $this->Html->tag('div', $unmatchableReplies.__(' unmatchable replies'), array('class' => 'ttc-title'));
	?>
	<div>
</div>
</div>


