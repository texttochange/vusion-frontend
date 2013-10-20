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
	$programStatsToCompute =array();
	foreach ($programs as $program): ?>
    
        <div id='<?php echo $program['Program']['url']; ?>' class='ttc-program-box' onclick="window.location.pathname='<?php echo '/'.$program['Program']['url']; ?>'">
        <?php $programName = $this->Text->truncate($program['Program']['name'], 
            24, 
            array('ellipsis' => '...',
            'exact' => true ));
        echo $this->Html->tag('div', $programName, array('class' => 'ttc-program-title','title' => $program['Program']['name']));
        if (isset($program['Program']['shortcode'])){
            $shortcode = $this->PhoneNumber->replaceCountryCodeOfShortcode(
                $program['Program']['shortcode'],
                $countryIndexedByPrefix);
            echo $this->Html->tag('div', $shortcode, array('class'=>'ttc-program-details'));
        }
        ?>
        <?php
		if (isset($program['Program']['shortcode'])) {
			echo '<div class ="ttc-program-stats">';
			$programStatsToCompute[] = $program;			
			echo '<div>';
			echo '<img src="/img/ajax-loader.gif">';
			echo '</div>';
			echo '</div>';
		}else{
			echo $this->Html->link('Configure Shortcode and TimeZone', 
				array('program' => $program['Program']['url'],
					'controller' => 'programSettings',
					'action' => 'index'
					),
				array('class' => 'configure-program-settings'));
		}
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
    <?php
		$this->Js->set('programs', $programStatsToCompute);
		$this->Js->get('document')->event(
				'ready',
				'loadProgramStats();             
				');
			?>
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


