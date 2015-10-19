<?php
    $this->RequireJs->scripts(array("ttc-utils", "jquery-ui-timepicker"));
    $containsFilter         = (isset($containsFilter) ? $containsFilter : false);
    $containsDataControlNav = (isset($containsDataControlNav) ? $containsDataControlNav : false);
?>
<div id="header-content-box">
	<div id="header-content" class="content-header">
	<div class="table" style="width:100%">
       <div class="heading">
           <div class="cell">
               <h3>
                   <?php echo $contentTitle; ?>
               </h3>
           </div>
           <div class="cell">
               <ul class="ttc-actions">
                    <?php foreach($contentActions as $contentAction) : ?>
                        <li>
                        <?php echo $contentAction; ?> 
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php if ($containsDataControlNav): ?>
    <div class="table" style="width:100%">
        <div class="row">
            <div class="ttc-data-control">
            <?php if (isset($findType)):?>
                <div class="cell tabs">
                <ul>
                    <li <?php echo ($findType === 'all'? 'class="selected"' : ""); ?>>
                        <a href="<?php echo $this->Html->url(array('program' => $programDetails['url'], 'action' => 'index')) ?>" >
                        <label><?php echo __("All") ?></label>
                        </a>
                    </li>
                    <li <?php echo ($findType === 'scheduled'? 'class="selected"' : ""); ?>>
                        <a href="<?php echo $this->Html->url(array('program' => $programDetails['url'], 'action' => 'index', 'type' => 'scheduled')) ?>" >
                        <label><?php echo __("Scheduled") ?></label>
                        </a>
                    </li>
                    <li <?php echo ($findType === 'drafted'? 'class="selected"' : ""); ?>>
                        <a href="<?php echo $this->Html->url(array('program' => $programDetails['url'], 'action' => 'index', 'type' => 'drafted')) ?>" >
                        <label><?php echo __("Drafted") ?></label>
                        </a>
                    </li>
                    <li <?php echo ($findType === 'sent'? 'class="selected"' : ""); ?>>
                        <a href="<?php echo $this->Html->url(array('program' => $programDetails['url'], 'action' => 'index', 'type' => 'sent')) ?>" >
                        <label><?php echo __("Sent") ?></label>
                        </a>
                    </li>
                </ul>
                </div>
            <?php endif;?>
            <?php if (isset($containsSpan)):?>
            <div class="cell tabs">
                <ul>
                    <li <?php echo ($containsSpan === 'keys'? 'class="selected"' : ""); ?>>
                        <a href="<?php echo $this->Html->url(array('program' => $programDetails['url'], 'action' => 'index')) ?>" >
                        <label><?php echo __("Keys/Values") ?></label>
                        </a>
                    </li>
                    <li <?php echo ($containsSpan === 'tables'? 'class="selected"' : ""); ?>>
                        <a href="<?php echo $this->Html->url(array('program' => $programDetails['url'], 'action' => 'indexTable')) ?>" >
                        <label><?php echo __("Tables") ?></label>
                        </a>
                    </li>
                </ul>
            </div>                
            <?php endif;?>
            <div id="data-control-nav" class="ttc-paging paging">
				    <?php
				    if (isset($this->Paginator)) {
				    	$count = $this->Paginator->counter('{:count}');
				    	if ($count === 'many') {
				    	    $countTitle = __('Loading...');
				    	    $count = '<img src="/img/ajax-loader.gif">';
				    	    $urlParameters = $this->params['url'];
				            //$urlParameters['parameter'] = $parameter;
				            $ajaxUrl = $this->Html->url(array(
				                'program' => $programDetails['url'], 
				                'action' => 'paginationCount',
				                'ext' => 'json',
				                '?' => $urlParameters));
				            $this->RequireJs->runLine('loadPaginationCount("' . $ajaxUrl . '");');
				    	} else {
				    	    $countTitle = $count;
				    	}
				    	echo "<span class='ttc-page-count' title ='$countTitle'>";
				        echo $this->Paginator->counter('<span id="paging-start">{:start}</span> - <span id="paging-end">{:end}</span> of ');
				        echo "<span id='paging-count'>".$this->BigNumber->replaceBigNumbers($count, 3)."</span>";       
				        echo "</span>";
				        echo $this->Paginator->prev(
				            '<', 
				            array(
				                'url'=> array(
				                    'program' => (isset($programDetails['url']) ? $programDetails['url'] : null),
				                    '?' => $this->params['url'])),
				                    null,
				                    array('class' => 'prev disabled'));
				        echo $this->Paginator->next(
				            '>',
				            array(
				                'url'=> array(
				                    'program' => (isset($programDetails['url']) ? $programDetails['url'] : null),
				                    '?' => $this->params['url'])),
				                    null,
				                    array('class' => 'next disabled'));
				    }
				    ?>
            </div>
            <?php
            if ($containsFilter) {
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
                        $this->RequireJs->runLine('loadFilterParameterOptions("' . $parameter . '", "' . $ajaxUrl . '");');
                        $filterParameterOptions[$parameter] = array("Loading...");
                    }
                }
                $this->Js->set('filterParameterOptions', $filterParameterOptions);
                
                echo $this->Form->create(null, array(
                    'type'=>'get', 
                    'url'=>array(
                        'program' => (isset($programDetails['url']) ? $programDetails['url'] : null),
                        'controller' => $controller,
                        'action'=>'index'), 
                    'id' => 'advanced_filter_form', 
                    'class' => 'ttc-advanced-filter'));
                if (isset($filterParams)) {
                    $this->RequireJs->runLine('
                        $("#advanced_filter_form").show();
                        createFilter(true, "'.$filterParams['filter_operator'].'",'.$this->Js->object($filterParams['filter_param']).');
                        ');
                }
                echo $this->Form->end(array('label' => 'Filter', 'class' => 'ttc-filter-submit'));       
                $this->Js->get('#advanced_filter_form')->event(
                    'submit',
                    '$(":input[value=\"\"]").attr("disabled", true);
                    return true;');
            }
		    ?>
            </div>
	    </div>
    </div>
    <?php endif;?>
	</div>
</div>