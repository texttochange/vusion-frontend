<div class="Program Logs index">
	<h3><?php echo __('Program Logs'); ?></h3>	
	
    <div class="ttc-table-display-area">
	<div class="ttc-table-scrolling-area display-height-size">
	<table cellpadding="0" cellspacing="0">
	    <thead>
	        <tr>
	            <th class="date-time">Date</th>
	            <th id="log-css">Log</th>
	        </tr>
	    </thead>
	    </tbody>
	        <?php foreach ($programLogs as $log): ?>
	        <?php
		        ?>
	        <tr>
	            <td class="date-time">
	            <?php 
	                $isoDate = substr($log, 1, 19);
	                $uiDate = $this->Time->format('d/m/Y H:i:s', $isoDate);
	                echo $uiDate;
	            ?></td>
	            <td >
	            <?php 
	            echo htmlspecialchars(substr($log, 21));
	            ?></td>
	        </tr>
	        <?php endforeach; ?>
	    </tbody>
	 </table>
	 </div>
	 </div>	
</div>

<?php echo $this->Js->writeBuffer(); ?>
