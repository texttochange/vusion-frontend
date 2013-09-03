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
			echo '<div class ="ttc-program-stats">';
			echo '<div>';
			if (isset($program['Program']['shortcode'])) {
				echo $this->Html->tag(
					'span',
					$program['Program']['stats']['active-participant-count'].'/'.
					$program['Program']['stats']['participant-count'],
					array('title' => __('Optin / Total participant(s)'), 'class' => 'stat'));	
				echo __(' participant(s)');
				echo '</div>';
				echo '<div>';
				echo $this->Html->tag(
					'span',
					$program['Program']['stats']['history-count'].'('.
					$program['Program']['stats']['total-current-month-messages-count'].')',
					array('title' => __('Total (total current month) message(s)'), 'class' => 'stat'));	
				echo __(' total message(s)');
				echo '</div>';
				echo '<div>';
				echo $this->Html->tag(
					'span',
					$program['Program']['stats']['all-received-messages-count'].'('.
					$program['Program']['stats']['current-month-received-messages-count'].')',
					array('title' => __('Total (current month) received - Total(current month) sent'), 'class' => 'stat'));	
				echo __(' received '); 
				echo $this->Html->tag(
					'span',
					$program['Program']['stats']['all-sent-messages-count'].'('.
					$program['Program']['stats']['current-month-sent-messages-count'].')',
					array('title' => __('Total (current month) received - Total(current month) sent'), 'class' => 'stat'));	
				echo __(' sent message(s)');
				echo '</div>';
				echo '<div>';
				echo $this->Html->tag(
					'span',
					$program['Program']['stats']['schedule-count'].'('.
					$program['Program']['stats']['today-schedule-count'].')',
					array('title' => __('Total (today) schedule(s)'), 'class' => 'stat'));	
				echo __(' schedule(s)'); 				
			}else{
				echo $this->Html->link('Configure Shortcode and TimeZone', 
					array('program' => $program['Program']['url'],
						'controller' => 'programSettings',
						'action' => 'index'
						),
					array('style'=>'text-decoration:none;font-weight:normal; font-size:14px; color:#C43C35;', 'class' => 'stat'));
			}
			echo '</div>';
			echo '</div>';
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


