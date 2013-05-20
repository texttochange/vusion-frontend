<div class='ttc-program-header'>
<div class="ttc-program-time">
		<?php
				if ($programTimezone) {
				        echo $this->Html->tag('span', $programTimezone.' - ');
				        $now = new DateTime('now');
				        date_timezone_set($now,timezone_open($programTimezone));
				        echo $this->Html->tag('span', $now->format('d/m/Y H:i:s'), array("id"=>"local-date-time") );
				        $this->Js->get('document')->event('ready',
				        		'setInterval("updateClock()", 1000);'
				        		);
				}
		?>
				</div>
				<div class='ttc-program-title'>
				<?php
				    echo $this->Html->link($programName, 
					array('program' => $programUrl,
					      'controller' => 'programHome',
					      'action' => 'index'
					      ), array('style'=>'text-decoration:none;font-weight:normal; font-size:22px'));
				
				    //echo " > ";
				?>
				</div>
				
				<div class="ttc-program-link">
				<?php
				echo $this->Html->link($shortCode, 
					array('program' => $programUrl,
					      'controller' => 'programHome',
					      'action' => 'index'
					      ),
				        array('style'=>'text-decoration:none;font-weight:normal; font-size:12px'));
				    echo "> ";
				    echo $this->Html->link($this->params['controller'], 
					array('program' => $programUrl,
					      'controller' => $this->params['controller'],
					      'action' => 'index'
					      ),
				        array('style'=>'text-decoration:none;font-weight:normal; font-size:12px'));
				    if(isset($this->params['action']) &&  $this->params['action'] != 'index') {
				        echo " > ";
					echo $this->Html->link($this->params['action'], 
					    array('program' => $programUrl,
						  'controller' => $this->params['controller'],
						  'action' => $this->params['action']
						  ),
				        array('style'=>'text-decoration:none;font-weight:normal; font-size:12px'));
				    }				    
				?>
				
				</div>
				</div>				
		
