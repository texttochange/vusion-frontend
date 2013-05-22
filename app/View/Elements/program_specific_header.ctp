<div class='ttc-program-header'>
	<div class="ttc-program-time">
		<?php
		if ($programDetails['timezone']) {
				echo $this->Html->tag('span', $programDetails['timezone'].' - ');
				$now = new DateTime('now');
				date_timezone_set($now,timezone_open($programDetails['timezone']));
				echo $this->Html->tag('span', $now->format('d/m/Y H:i:s'), array("id"=>"local-date-time") );
				$this->Js->get('document')->event('ready',
						'setInterval("updateClock()", 1000);'
						);
		}
		?>
	</div>
	<div class='ttc-program-title'>
		<?php
		echo $this->Html->link($programDetails['name'], 
				array('program' => $programDetails['url'],
						'controller' => 'programHome',
						'action' => 'index'
						), array('style'=>'text-decoration:none;font-weight:normal; font-size:22px'));
		echo " " ;
		 echo  '<l class = "blackets"> (</l>' ;
		
		$prefix = $this->PhoneNumber->getInternationalPrefix(
			         $programDetails['shortcode'],
			         $countriesIndexes);
		$countryAndShortcode = $this->PhoneNumber->displayCode(
			        $programDetails['shortcode'],
			         $prefix,
			         $countriesIndexes);		
		echo $this->Html->link($countryAndShortcode, 
				array('program' => $programDetails['url'],
						'controller' => 'programHome',
						'action' => 'index'
						),
				array('style'=>'text-decoration:none;font-weight:normal; font-size:14px'));
		echo  '<l class = "blackets">) </l>' ;
		?>
	</div>
	<div class="ttc-program-link">
		<?php
				echo $this->Html->link($this->params['controller'], 
				array('program' => $programDetails['url'],
						'controller' => $this->params['controller'],
						'action' => 'index'
						),
				array('style'=>'text-decoration:none;font-weight:normal; font-size:12px'));
		if(isset($this->params['action']) &&  $this->params['action'] != 'index') {
				echo " > ";
				echo $this->Html->link($this->params['action'], 
						array('program' => $programDetails['url'],
								'controller' => $this->params['controller'],
								'action' => $this->params['action']
								),
						array('style'=>'text-decoration:none;font-weight:normal; font-size:12px'));
		}				    
		?>
	</div>
</div>				

