<div class="ttc-data-control">
    <div id="data-control-nav" class="ttc-paging paging">
    <?php
    if (isset($this->Paginator)) {
    	$count = $this->Paginator->counter('{:count}');
    	echo "<span class='ttc-page-count' title = $count>";
        echo $this->Paginator->counter(array('format'=> __('{:start} - {:end} of ')));
        echo $this->BigNumber->replaceBigNumbers($count);       
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
    $this->Js->set('filterParameterOptions', $filterParameterOptions);
    
    echo $this->Form->create(null, array(
        'type'=>'get', 
        'url'=>array(
            'program' => (isset($programDetails['url']) ? $programDetails['url'] : null),
            'controller' => $controller,
            'action'=>'index'), 
        'id' => 'advanced_filter_form', 
        'class' => 'ttc-advanced-filter'));
    if (isset($this->params['url']['filter_operator']) && isset($this->params['url']['filter_param'])) {
        $this->Js->get('document')->event(
            'ready',
            '$("#advanced_filter_form").show();
            createFilter(true, "'.$this->params['url']['filter_operator'].'",'.$this->Js->object($this->params['url']['filter_param']).');
            ');
    }
    echo $this->Form->end(array('label' => 'Filter', 'class' => 'ttc-filter-submit'));       
    $this->Js->get('#advanced_filter_form')->event(
        'submit',
        '$(":input[value=\"\"]").attr("disabled", true);
        return true;');
    ?>
</div>