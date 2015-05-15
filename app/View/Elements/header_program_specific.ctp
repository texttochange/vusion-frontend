<div id="header-program">
	<div class="table" style="width:98%;padding-left:1.5em;padding-right:1.5em">
        <div class="heading">
                <div class="cell">
                    <span class='ttc-program-title'>
                        <?php
                        echo $this->Html->link($programDetails['name'], 
                            array('program' => $programDetails['url'],
                                'controller' => 'programHome',
                                'action' => 'index'
                                ),
                            array(
                                'style'=>'text-decoration:none;font-weight:normal; font-size:22px'));
                        ?>
                    </span>
                    <?php if ($programDetails['status']==='running') : ?>
                    <span class = "code">
                        <?php
                        echo  '<l class = "blackets"> (</l>';
                        if (isset($programDetails['settings']['shortcode'])) {
                            $countryAndShortcode = $this->PhoneNumber->replaceCountryCodeOfShortcode(
                                $programDetails['settings']['shortcode'],
                                $countryIndexedByPrefix);
                            echo '<l class ="blackets">'.$countryAndShortcode.'</l>';
                        } else {
                            echo $this->Html->link(__('configure Shortcode'), 
                                array(
                                    'program' => $programDetails['url'],
                                    'controller' => 'programSettings',
                                    'action' => 'index'),
                                array(
                                    'style'=>'text-decoration:none;font-weight:normal; font-size:14px; color:#C43C35'));
                        }
                        echo  '<l class = "blackets">)</l>';
                        ?>
                    </span>
                    <?php endif; ?>
                    <span class="cell ttc-program-link">
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
                    </span>
				</div>
                <div class="cell ttc-program-time">
                    <?php
                    if (isset($programDetails['settings']['timezone'])) {
                        echo $this->Html->tag('span', $programDetails['settings']['timezone'].' - ');
                        $now = new DateTime('now');
                        date_timezone_set($now,timezone_open($programDetails['settings']['timezone']));
                        echo $this->Html->tag('span', $now->format('d/m/Y H:i:s'), array("id"=>"local-date-time") );
                        $this->Js->get('document')->event(
                            'ready',
                            'setInterval("updateClock()", 1000);');
                    } else {
                        echo $this->Html->link('configure Timezone', 
                            array(
                                'program' => $programDetails['url'],
                                'controller' => 'programSettings',
                                'action' => 'index'
                                ),
                            array(
                                'style'=>'text-decoration:none;font-weight:normal; font-size:14px; color:#C43C35; padding-right:128px'));
                    }
                    ?>
                </div>
        </div>
    </div>
</div>