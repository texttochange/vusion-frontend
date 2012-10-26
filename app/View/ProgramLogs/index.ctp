<div class="Program Logs index">
	<h3><?php echo __('Program Logs'); ?></h3>	
  <div class="ttc-display-area">
	<table cellpadding="0" cellspacing="0">
	<tr>
	    <th>Date</th>
	    <th>Log</th>
	</tr>
	 <?php foreach ($programLogs as $key=>$log): ?>
	 <?php
          $newDate = $this->Time->format('d/m/Y H:i:s', substr($key, 1, 19));
          $newKey = substr_replace($key, $newDate, 1, 19);
	 ?>
	 <tr>
	   <td><?php echo substr($newKey, 1, 19); ?>
	   </td>
	   <td><?php echo htmlspecialchars(substr($newKey, 21)); ?></td>
	 </tr>
	 <?php endforeach; ?>
	 </table>
  </div>	
</div>

<?php echo $this->Js->writeBuffer(); ?>
