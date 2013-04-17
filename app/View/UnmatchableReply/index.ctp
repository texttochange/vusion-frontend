<div class="unmatchable replies index">
    <ul class="ttc-actions">
        <li>
        <?php 
        echo $this->Html->tag(
            'span', 
            __('Filter'), 
            array('class' => 'ttc-button', 'name' => 'add-filter')); 
        $this->Js->get('[name=add-filter]')->event(
            'click',
            '$("#advanced_filter_form").show();
            createFilter();
            addStackFilter();');
		?> 
		</li>
	</ul>
	<h3><?php echo __('Unmatchable Replies');?></h3>
	<?php
	    echo $this->element('filter_box', array(
	        'controller' => 'unmatchableReply'));
	?>
	<div class="ttc-table-display-area">
	<div class="ttc-table-scrolling-area">
	<table cellpadding="0" cellspacing="0">
	    <thead>
	        <tr>                                                                        
			    <th id="phone-css"><?php echo $this->Paginator->sort('participant-phone', __('From'));?></th>
			    <th id="direction-css"><?php echo $this->Paginator->sort('to', __('To'));?></th>
			    <th id="message-css"><?php echo $this->Paginator->sort('message-content', __('Message'));?></th>
			    <th id="date-time-css"><?php echo $this->Paginator->sort('timestamp', __('Time'));?></th>
			 </tr>
		</thead>
		<tbody>
			 <?php
			 foreach($unmatchableReplies as $unmatchableReply):
			 ?>
			 <tr>
			     <?php 
			     echo '<td id="phone-css">';
			     $prefix = $this->PhoneNumber->getInternationalPrefix(
			         $unmatchableReply['UnmatchableReply']['participant-phone'],
			         $countriesIndexes);
			     echo $this->PhoneNumber->replaceCountryCodeOfShortcode(
			         $unmatchableReply['UnmatchableReply']['participant-phone'],
			         $countriesIndexes); 
			     echo '&nbsp;</td>';
			     echo '<td id="direction-css">';
			     $to = $this->PhoneNumber->addInternationalCodeToShortcode(
			         $unmatchableReply['UnmatchableReply']['to'],
			         $prefix);
			     echo $this->PhoneNumber->replaceCountryCodeOfShortcode(
			         $to,
			         $countriesIndexes);
			     echo '&nbsp;</td>';
			     ?>
			     <td id="message-css"><?php echo $unmatchableReply['UnmatchableReply']['message-content']; ?>&nbsp;</td>
			     <td id="date-time-css"><?php echo $this->Time->format('d/m/Y h:i', $unmatchableReply['UnmatchableReply']['timestamp']); ?>&nbsp;</td>
			 </tr>
			 <?php endforeach; ?>
	    </tbody>
	</table>
	</div>
	</div>

</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
	    <li><?php echo $this->Html->link(__('Back To Program List'),
			array('controller' => 'programs', 
                        'action' => 'index'));
            ?></li>
	</ul>
</div>
