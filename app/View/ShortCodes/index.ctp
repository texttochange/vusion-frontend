<div class="shortcode-index">
	<h3><?php echo __('ShortCodes');?></h3>
	<div class="ttc-data-control">
	<div id="data-control-nav" class="ttc-paging paging">
	<?php
	echo "<span class='ttc-page-count'>";
	echo $this->Paginator->counter(array(
	    'format' => __('{:start} - {:end} of {:count}')
	    ));
	echo "</span>";
	echo $this->Paginator->prev('<', array('url'=> array('program' => $programUrl, '?' => $this->params['url'])), null, array('class' => 'prev disabled'));
	//echo $this->Paginator->numbers(array('separator' => ''));
	echo $this->Paginator->next(' >', array('url'=> array('program' => $programUrl, '?' => $this->params['url'])), null, array('class' => 'next disabled'));
	?>
	</div>
	</div>
	<div class="ttc-table-display-area-shortcode">
	<div class="ttc-table-scrolling-area">
	<table cellpadding="0" cellspacing="0">
	    <thead>
	        <tr>
	            <th class="phone"><?php echo $this->Paginator->sort('shortcode', __('Shortcode'));?></th>
	            <th class="country"><?php echo $this->Paginator->sort('country', __('Country'));?></th>
	            <th class="prefix"><?php echo $this->Paginator->sort('international-prefix', __('International Prefix'));?></th>
	            <th class="support"><?php echo $this->Paginator->sort('support-customized-id', __('Support Customized Id'));?></th>
	            <th class="support"><?php echo $this->Paginator->sort('supported-internationally', __('Supported Internationally'));?></th>
	            <th class="action"><?php echo __('Actions');?></th>
	         </tr>
	     </thead>
	     <tbody>	
	         <?php foreach ($shortcodes as $shortcode): ?>
	         <tr>		
	         <td class="phone"><?php echo $shortcode['ShortCode']['shortcode']; ?>&nbsp;</td>
	         <td class="country"><?php echo $shortcode['ShortCode']['country']; ?>&nbsp;</td>
	         <td class="prefix"><?php echo $shortcode['ShortCode']['international-prefix']; ?>&nbsp;</td>
	         <td class="support"><?php echo ($shortcode['ShortCode']['support-customized-id']? __('yes'):__('no')); ?>&nbsp;</td>
	         <td class="support"><?php echo ($shortcode['ShortCode']['supported-internationally']? __('yes'):__('no')); ?>&nbsp;</td>
	         <td class="actions action">
	             <?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $shortcode['ShortCode']['_id'])); ?>
	             <?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $shortcode['ShortCode']['_id']), null, __('Are you sure you want to delete the shortcode "%s"?', $shortcode['ShortCode']['shortcode'])); ?>
	         </td>
	         </tr>
	         <?php endforeach; ?>
	      </tbody>
	</table>
	</div>
	<!--<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</p>
	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>-->
	</div>
    </div>
    <div class="admin-action">
    <div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New ShortCode'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index')); ?></li>
	</ul>
</div>
</div>
