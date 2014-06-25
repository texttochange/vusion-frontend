<div class="ttc-data-control">
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
            $this->Js->get('document')->event(
                'ready',
                'loadPaginationCount("' . $ajaxUrl . '");'
            );
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
    
    echo $this->Form->create(null, array(
        'type'=>'get', 
        'url'=>array(
            'program' => (isset($programDetails['url']) ? $programDetails['url'] : null),
            'controller' => $controller,
            'action'=>'index'), 
        'id' => 'advanced_filter_form', 
        'class' => 'ttc-advanced-filter'));
    //if (isset($this->params['url']['filter_operator']) && isset($this->params['url']['filter_param'])) {
    if (isset($filterParams)) {
        $this->Js->get('document')->event(
            'ready',
            '$("#advanced_filter_form").show();
            createFilter(true, "'.$filterParams['filter_operator'].'",'.$this->Js->object($filterParams['filter_param']).');
            ');
    }
    echo $this->Form->end(array('label' => 'Filter', 'class' => 'ttc-filter-submit'));       
    $this->Js->get('#advanced_filter_form')->event(
        'submit',
        '$(":input[value=\"\"]").attr("disabled", true);
        return true;');
    ?>
</div>