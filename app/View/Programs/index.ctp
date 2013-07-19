<div class="ttc-program-index">
    <?php echo $this->AclLink->generateButton(
            __('Create Program'), 
            null,
            'programs',
            'add',
            array('class' => 'ttc-button', 'style'=>'float:right'));
        echo $this->Html->tag(
            'span', 
            __('Filter'), 
            array('class' => 'ttc-button', 'style'=>'float:right', 'name' => 'add-filter')); 
        $this->Js->get('[name=add-filter]')->event(
            'click',
            '$("#advanced_filter_form").show();
            createFilter();
            addStackFilter();');
    ?>
    <h3><?php echo __('Programs');?></h3>
    <?php
	    echo $this->element('filter_box', array(
	        'controller' => 'programs'));
	    $this->Js->get('document')->event('ready', '$(".ttc-paging").css("margin-right", "0px");');
	?>
    <div style="clear:both">
       <!-- Buffer zone -->
    </div>
	<?php
	if (preg_grep('/^filter/', array_keys($this->params['url'])) && empty($programs))
	    echo "No results found.";
	foreach ($programs as $program): ?>

	<div class='ttc-program-box' onclick="window.location.pathname='<?php echo '/'.$program['Program']['url']; ?>'">
	<?php $programName = $this->Text->truncate($program['Program']['name'], 
			24, 
			array('ellipsis' => '...',
			'exact' => true ));
	echo $this->Html->tag('div', $programName, array('class' => 'ttc-program-title','title' => $program['Program']['name']));
	?>
		<?php
		if (isset($program['Program']['shortcode']))
		    echo $this->Html->tag('div', $program['Program']['shortcode'], array('class'=>'ttc-program-details')); ?>
		<?php	
			echo $this->Html->tag(
				'div',				
				'<b title = "Active participants / Total participants">'.$program['Program']['stats']['active-participant-count'].'/'.
				$program['Program']['stats']['participant-count'].'</b> '.__(' participant(s)').'<br/>'.
				'<b title = "Total messages(Total current month)">'.$program['Program']['stats']['history-count'].'('.
				$program['Program']['stats']['total-current-month-messages-count'].') </b>'.__(' total message(s)').'<br/>'.
				'<b title = "Total received(current month) - Total sent(current month)">'.$program['Program']['stats']['all-received-messages-count'].'('.
				$program['Program']['stats']['current-month-received-messages-count'].') </b>'.__('received').' - <b>'. 
				$program['Program']['stats']['all-sent-messages-count'].'('.
				$program['Program']['stats']['current-month-sent-messages-count'].')</b>'.__(' sent message(s)').'<br/>'.		         
				'<b title = "Total schedules(today)">'.$program['Program']['stats']['schedule-count'].'('.
				$program['Program']['stats']['today-schedule-count'].')</b>'.__(' schedule(s)'),
				array('class'=>'ttc-program-stats')
				);
		?>
		<?php if ($isProgramEdit) { ?>
		<div class="ttc-program-quicklinks">
			<?php echo $this->Html->link(__('Admin'), array('action' => 'edit', $program['Program']['id'])); ?>
			<br>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $program['Program']['id']), array('name'=>'delete-program'), __('Are you sure you want to delete %s?', $program['Program']['name'])); ?>
		</div>
		<?php };
		$this->Js->get("[name='delete-program']")->event("click", "event.stopPropagation()");
		?>
	</div>
    <?php endforeach; ?>
</div>
<?php echo $this->Js->writeBuffer(); ?>

<div class="ttc-recent-issues">
	<h3><?php echo __('Recent Issues'); ?></h3>
	<ul class="ttc-issues-list">
	<?php foreach ($unmatchableReplies as $unmatchableReply): ?>
	<li>
	<?php	    
	    echo $this->Html->tag('div', $this->Time->format('d/m/Y H:i:s', $unmatchableReply['UnmatchableReply']['timestamp']), array('class' => 'ttc-issue-time'));
	    echo "<div class='ttc-issue-content'>";
	    echo $this->Html->tag('h3', $this->Html->link(__('unmatchable reply'),array('controller'=>'unmatchableReply','action' => 'index')));
	    echo $this->Html->tag('p', ($unmatchableReply['UnmatchableReply']['message-content']!=null ? $unmatchableReply['UnmatchableReply']['message-content'] : "<i>message empty</i>"));
	    echo "</div>";
	?>
	</li>
	<?php endforeach; ?>
	</ul>
</div>


