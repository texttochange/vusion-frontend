<div class="users-index index width-size">
    <h3><?php echo __('Users Logs');?></h3>
    <div class="ttc-data-control">
	<div id="data-control-nav" class="ttc-paging paging">
        <?php
            echo "<span class='ttc-page-count'>";
            echo $this->Paginator->counter(array(
                'format' => __('{:start} - {:end} of {:count}')
                ));
            echo "</span>";
            echo $this->Paginator->prev('<', array(), null, array('class' => 'prev disabled'));
            echo $this->Paginator->next(' >', array(), null, array('class' => 'next disabled'));
        ?>
	</div>
	</div>    
    <div class="ttc-table-display-area ">
	<div class="ttc-table-scrolling-area display-height-size">
	    <table class="user-logs" cellpadding="0" cellspacing="0">
	        <thead>
	            <tr>
			        <th class="date-time"><?php echo $this->Paginator->sort('timestamp',__('Timestamp'));?></th>
			        <th class="user_logs_field"><?php echo $this->Paginator->sort('timezone', __('Timezone'));?></th>
			        <th class="user_logs_field"><?php echo $this->Paginator->sort('user-id',__('User'));?></th>
			        <th class="user_logs_field"><?php echo $this->Paginator->sort('program-database-name', __('Program'));?></th>
			        <th class="user_logs_field"><?php echo __('Event');?></th>
			    </tr>
	        </thead>
	        <tbody>
	            <?php foreach ($userLogs as $userLog): ?>
	            <tr>
                    <td class="date-time"><?php echo $this->Time->format('d/m/Y H:i:s',$userLog['UserLog']['timestamp']); ?></td>
                    <td class="user_logs_field"><?php echo $userLog['UserLog']['timezone']; ?></td>
                    <td class="user_logs_field"><?php echo $userLog['UserLog']['user-name']; ?></td>
                    <td class="user_logs_field">
                    <?php
                    if (isset($userLog['UserLog']['program-name'])) {
                        echo $userLog['UserLog']['program-name'];
                    } else {
                        echo ' ';
                    }                    
                    ?>
                    </td>
                    <td class="user_logs_field"><?php echo $userLog['UserLog']['parameters']; ?></td>
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
		<li><?php echo $this->AclLink->generateButton(__('Back to Programs'), null, 'programs', 'index'); ?></li>
	</ul>
</div>
</div>	
