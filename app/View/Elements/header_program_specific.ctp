<div id="header-program">
	<div class="ttc-program-time">
	    <?php
	    if (isset($programDetails['settings']['timezone'])) {
	            echo $this->Html->tag('span', $programDetails['settings']['timezone'].' - ');
	            $now = new DateTime('now');
	            date_timezone_set($now,timezone_open($programDetails['settings']['timezone']));
	            echo $this->Html->tag('span', $now->format('d/m/Y H:i:s'), array("id"=>"local-date-time") );
	            $this->Js->get('document')->event('ready',
	                    'setInterval("updateClock()", 1000);'
	                    );
	    }else{
	            echo $this->Html->link('configure Timezone', 
	                    array('program' => $programDetails['url'],
	                            'controller' => 'programSettings',
	                            'action' => 'index'
	                            ),
	                    array('style'=>'text-decoration:none;font-weight:normal; font-size:14px; color:#C43C35; padding-right:128px'));
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
	    if ($programDetails['status']==='running') {
	        echo  '<l class = "blackets"> (</l>';
	        if (isset($programDetails['settings']['shortcode'])) {
	            $countryAndShortcode = $this->PhoneNumber->replaceCountryCodeOfShortcode(
	                $programDetails['settings']['shortcode'],
	                $countryIndexedByPrefix);
	        }
	        if (isset($countryAndShortcode)){
	                echo '<l class ="blackets">'.$countryAndShortcode.'</l>';
	        } else {
	                echo $this->Html->link(
	                    __('configure Shortcode'), 
	                    array('program' => $programDetails['url'],
	                            'controller' => 'programSettings',
	                            'action' => 'index'
	                            ),
	                    array('style'=>'text-decoration:none;font-weight:normal; font-size:14px; color:#C43C35'));
	        }                
	        echo  '<l class = "blackets">)</l>';
	    }
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