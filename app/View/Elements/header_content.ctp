<div style="height:4em">
	<div id="header-content">
		<table class="width-size">
		    <thead>
		        <tr>
		            <th>
		                <h3>
		           		<?php echo $content_title; ?>
		                </h3>
		            </th> 
		            <th>
		                <ul class="ttc-actions">
		                    <?php foreach($content_actions as $content_action) : ?>
		                  	<li>
		                  	<?php echo $content_action; ?> 
		              	  	</li>
		              	  	<?php endforeach; ?>
		              	  	<li>
		               </ul>
		            </th>
		        </tr>
		    </thead>
		</table>
	</div>
</div>