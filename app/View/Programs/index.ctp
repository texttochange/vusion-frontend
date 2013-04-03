
<div class="ttc-program-index">
    <?php echo $this->AclLink->generateButton(
            __('Create Program'), 
            null,
            'programs',
            'add',
            array('class' => 'ttc-button', 'style'=>'float:right')); ?>
    <h3><?php echo __('Programs');?></h3>
    <div class="paging" style="text-align:right">
    <?php
    echo "<span class='ttc-page-count'>";
    if (isset($this->Paginator)) {
        echo $this->Paginator->counter(array(
            'format' => __('{:start} - {:end} of {:count}')
            ));
        echo "</span>";
        echo $this->Paginator->prev('<', null, null, array('class' => 'prev disabled'));
        echo $this->Paginator->next('>', null, null, array('class' => 'next disabled'));
    }
    ?>
    </div>
	<?php
	foreach ($programs as $program): ?>
	<div class='ttc-program-box' title= "<?php  echo $program['Program']['name']?> " onclick="window.location.pathname='<?php echo '/'.$program['Program']['url']; ?>'">	
	
	<?php $programName = $this->Text->truncate($program['Program']['name'],
	    20,
	    array('ellipsis' =>'....', 
	        'exact'=> true )); 
	    echo $this->Html->tag('div', $programName, array('class'=>'ttc-program-title') );
	    ?>	
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


