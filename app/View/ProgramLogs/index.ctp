<div>
	
<div class="Program Logs index">
	<h3><?php echo __('Program Logs'); ?></h3>
	
	<div class="ttc-display-area">
	<table cellpadding="0" cellspacing="0">
	 <?php foreach ($programLogs as $key=>$log): ?>
	 <tr>
	   <td>
	      <?php
	          $newDate = $this->Time->format('d/m/Y H:i:s', substr($key, 1, 19));
	          $newKey = substr_replace($key, $newDate, 1, 19);
	          echo $newKey."<br />";
	      ?>
	   </td>
	 </tr>
	 <?php endforeach; ?>
	 </table>
	 </div>
	<p>
		
	</p>

	<div class="paging">
	
        </div>
	
</div>

<?php echo $this->Js->writeBuffer(); ?>
