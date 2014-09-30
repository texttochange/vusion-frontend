<div class="users-index">
    <h3><?php echo __('Users Logs');?></h3>
    <div class="ttc-table-display-area">
	<div class="ttc-table-scrolling-area display-height-size">
	    <table cellpadding="0" cellspacing="0">
	        <thead>
	            <tr>
			        <th class="date-time"><?php echo __('Date');?></th>
			        <th ><?php echo __('Timezone');?></th>
			        <th ><?php echo __('User Name');?></th>
			        <th ><?php echo __('UserID');?></th>
			        <th ><?php echo __('Program Name');?></th>
			        <th ><?php echo __('Datebase Name');?></th>
			        <th ><?php echo __('Controller');?></th>
			        <th ><?php echo __('Action');?></th>
			        <th ><?php echo __('Event');?></th>
			    </tr>
	        </thead>
	        <tbody>
	            <?php foreach ($userLogs as $userLog): ?>
	            <tr>
                    <td class="date-time"><?php echo $this->Time->format('d/m/Y H:i:s',$userLog['UserLog']['timestamp']); ?></td>
                    <td ><?php echo $userLog['UserLog']['timezone']; ?></td>
                    <td ><?php echo $userLog['UserLog']['user-name']; ?></td>
                    <td ><?php echo $userLog['UserLog']['user-id']; ?></td>
                    <td ><?php echo $userLog['UserLog']['program-name']; ?></td>
                    <td ><?php echo $userLog['UserLog']['program-database-name']; ?></td>
                    <td ><?php echo $userLog['UserLog']['controller']; ?></td>
                    <td ><?php echo $userLog['UserLog']['action']; ?></td>
                    <td ><?php echo $userLog['UserLog']['parameters']; ?></td>
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
