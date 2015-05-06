<div id="header-program">
	<table style="width:100%;padding-left:1.5em;padding-right:1.5em">
		<thead>
			<tr>
				<th>
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
					if (isset($programDetails['settings']['shortcode'])) {
						$countryAndShortcode = $this->PhoneNumber->replaceCountryCodeOfShortcode(
							$programDetails['settings']['shortcode'],
							$countryIndexedByPrefix);
						echo $countryAndShortcode;
					} else {
						echo $this->Html->link(__('configure Shortcode'), 
							array(
								'program' => $programDetails['url'],
								'controller' => 'programSettings',
								'action' => 'index'),
							array(
								'style'=>'text-decoration:none;font-weight:normal; font-size:14px; color:#C43C35'));
					}
					?>
				    </span>
				<?php endif; ?>
				</th>
				<th class="ttc-program-time">
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
				</th>
			</tr>
		</thead>
	</table>
</div>
