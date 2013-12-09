<div class="users-index">
    <ul class="ttc-actions">
        <li>
        <?php 
        echo $this->Html->tag(
            'span', 
            __('Filter'), 
            array('class' => 'ttc-button', 'name' => 'add-filter')); 
        $this->Js->get('[name=add-filter]')->event(
            'click',
            '$("#advanced_filter_form").show();
            createFilter();
            addStackFilter();');
		?> 
		</li>
	</ul>
	<h3><?php echo __('Credit Viewer');?></h3>
	<div class="ttc-data-control">
        <div id="data-control-nav" class="ttc-paging paging">
            <?php
                echo "<span class='ttc-page-count'>";
                echo $this->Paginator->counter(array(
                    'format' => __('{:start} - {:end} of {:count}')
                    ));
                echo "</span>";
                echo $this->Paginator->prev('<', array(), null, array('class' => 'prev disabled'));
                //echo $this->Paginator->numbers(array('separator' => ''));
                echo $this->Paginator->next(' >', array(), null, array('class' => 'next disabled'));
            ?>
        </div>
        <?php
            $this->Js->set('filterFieldOptions', $filterFieldOptions);
            foreach ($filterParameterOptions as $parameter => &$options) {
                if (isset($options['_ajax'])) {
                    $urlParameters = $this->params['url'];
                    $urlParameters['parameter'] = $parameter;
                    $ajaxUrl = $this->Html->url(array(
                        'program' => $programDetails['url'], 
                        'action' => 'getFilterParameterOptions',
                        'ext' => 'json',
                        '?' => $urlParameters));
                    $this->Js->get('document')->event(
                        $options['_ajax'],
                        'loadFilterParameterOptions("' . $parameter . '", "' . $ajaxUrl . '");'
                    );
                    $filterParameterOptions[$parameter] = array("Loading...");
                }
            }
            $this->Js->set('filterParameterOptions', $filterParameterOptions);
            
            $url = $this->Html->url(array('controller'=>'creditViewer', 'action' => 'index'));
            echo $this->Html->useTag(
                'form',
                $url,
                array('method' => 'get',
                    'id' => 'advanced_filter_form',
                    'class' => 'ttc-advanced-filter')
                
            );
            echo $this->Form->end(array('label' => 'Filter', 'class' => 'ttc-filter-submit'));
            if (isset($this->params['url']['filter_operator']) && isset($this->params['url']['filter_param'])) {
                $this->Js->get('document')->event(
                    'ready',
                    '$("#advanced_filter_form").show();
                    createFilter(true, "'.$this->params['url']['filter_operator'].'",'.$this->Js->object($this->params['url']['filter_param']).');
                    ');
            }
            $this->Js->get('#advanced_filter_form')->event(
                'submit',
                '$(":input[value=\"\"]").attr("disabled", true);
                return true;');
        ?>
	</div>
	<div class="ttc-table-display-area">
	<div class="ttc-table-scrolling-area display-height-size">
	<table cellpadding="0" cellspacing="0">
	    <thead>
	        <tr>
			    <th class="prefix"><?php echo $this->Paginator->sort('program');?></th>
			    <th class="prefix"><?php echo $this->Paginator->sort('shortcode');?></th>
			    <th class="details"><?php echo $this->Paginator->sort('total credits');?></th>
			</tr>
		</thead>
		<tbody>
		    <?php foreach ($programs as $program): ?>
		    <tr>
		        <td class="prefix"><?php echo h($program['Program']['name']); ?>&nbsp;</td>
		        <td class="prefix">
		            <?php 
		                if (isset($program['Program']['shortcode'])) {
		                    $shortcode = $this->PhoneNumber->replaceCountryCodeOfShortcode(
                                $program['Program']['shortcode'],
                                $countryIndexedByPrefix);
		                    echo __($shortcode);
		                }
		            ?>
                </td>
		        <td class="details">
		            <?php 
		                if (isset($program['Program']['total-credits'])) {
		                    echo h($program['Program']['total-credits']);
		                } else {
		                    echo __('<i>Unlimited</i>');
		                }
		            ?>
		        </td>
		    </tr>
		   <?php endforeach; ?>
		 </tbody>
	</table>
	</div>
	</div>	
	</div>
	<div class="admin-action">
	<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index')); ?></li>
	</ul>
</div>
</div>
