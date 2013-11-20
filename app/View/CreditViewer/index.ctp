<div class="users-index">
	<h3><?php echo __('Credit Viewer');?></h3>
	<div class="ttc-data-control">
	<div id="data-control-nav" class="ttc-paging paging">
	<?php
	echo "<span class='ttc-page-count'>";
	echo $this->Paginator->counter(array(
	    'format' => __('{:start} - {:end} of {:count}')
	    ));
	echo "</span>";
	echo $this->Paginator->prev('<', array(), null, array('class' => 'prev disabled'));
	//echo $this->Paginator->numbers(array('separator' => ''));
	echo $this->Paginator->next(' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
	</div>
	<div class="ttc-table-display-area">
	<div class="ttc-table-scrolling-area display-height-size">
	<table cellpadding="0" cellspacing="0">
	    <thead>
	        <tr>
			    <th class="prefix"><?php echo $this->Paginator->sort('program');?></th>
			    <th class="prefix"><?php echo $this->Paginator->sort('shortcode');?></th>
			    <th class="details"><?php echo $this->Paginator->sort('total credits');?></th>
			</tr>
		</thead>
		<tbody>
		    <?php foreach ($programs as $program): ?>
		    <tr>
		        <td class="prefix"><?php echo h($program['Program']['name']); ?>&nbsp;</td>
		        <td class="prefix">
		            <?php 
		                if (isset($program['Program']['shortcode'])) {
		                    $shortcode = $this->PhoneNumber->replaceCountryCodeOfShortcode(
                                $program['Program']['shortcode'],
                                $countryIndexedByPrefix);
		                    echo __($shortcode);
		                }
		            ?>
                </td>
		        <td class="details">
		            <?php 
		                if (!empty($program['Program']['total-credits'])) {
		                    echo h($program['Program']['total-credits']);
		                } else {
		                echo __('No Limit');
		                }
		            ?>
		        </td>
		    </tr>
		   <?php endforeach; ?>
		 </tbody>
	</table>
	</div>
	</div>	
	</div>
	<div class="admin-action">
	<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index')); ?></li>
	</ul>
</div>
</div>
