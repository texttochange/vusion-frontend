<div>
	
<div class="Program Logs index">
	<h3><?php echo __('Program Logs'); ?></h3>
	 	
	<p>
		
	</p>

	<div class="paging">
	
        </div>
	
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Back Homepage'), array('program'=>$programUrl,'controller'=>'programHome')); ?></li>
	</ul>
</div>	
<?php echo $this->Js->writeBuffer(); ?>
