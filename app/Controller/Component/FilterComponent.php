<?php
App::uses('Component', 'Controller');

class FilterComponent extends Component 
{
    
    var $localizedValueLabel = array();
    
    public function __construct(ComponentCollection $collection, $settings = array())
    {
        $this->localizedValueLabel = array(
            'not-with' => __('not with'),
            'not-is-any'=>  __('not is any'),
            'not-is'=>  __('not is'),
            'now' => __('now'),
            'name' => __('program name'),
            'start-with' => __('start with'),
            'start-with-any' => __('start with any'),
            'is-not' =>  __('is not'),
            'is'=>  __('is'),
            'is-any'=>  __('is any'),
            'in'=> __('in'),
            'not-in'=>  __('not in'),
            'equal-to' => __('equal to'),
            'enrolled' => __('enrolled'),
            'date-from'=>  __('date from'),
            'date-to'=>  __('date to'),
            'dialogue-source' => __('dialogue source'),
            'interaction-source' => __('interaction source'),
            'request-source' => __('request source'),
            'from'=> __('from'),
            'from-phone' => __('from number'),
            'to-phone' => __('to number'),
            'to'=>  __('to'), 
            'contain'=>  __('contain'),
            'country' => __('country'),
            'has-keyword'=>  __('has keyword'), 
            'has-keyword-any'=>  __('has keyword any'), 
            'matching'=>  __('matching'),
            'not-matching'=>  __('not matching'),
            'message-direction' => __('message direction'),
            'message-status' => __('message status'),
            'message-content' => __('message content'),
            'separate-message' => __('separate message'),
            'shortcode' => __('shortcode'),           
            'not-matching'=> __('not matching'),
            'date' => __('date'),
            'many'=>  __('many'),
            'any'=>  __('any'),
            'all'=> __('all'),
            'with' => __('with'),
            'phone' => __('phone'),
            'participant-phone' => __('participant phone'),
            'tagged' => __('tagged'),
            'labelled' => __('labelled'),
            'optin' => __('optin'),
            'optout' => __('optout'),
            ); 
        $this->Controller = $collection->getController();
        parent::__construct($collection, $settings);
    }
    
    
    public function initialize(Controller $controller)
    {
        parent::startup($controller);
        $this->Controller = $controller;
    }
    
    
    public function getConditions($filterModel = null, $defaultConditions = null, $countryPrefixes =  null)
    {       
        $filter = array_intersect_key($this->Controller->params['url'], array_flip(array('filter_param', 'filter_operator')));
        
        if (!isset($filter['filter_param'])) 
            return $defaultConditions;
        
        if (!isset($filter['filter_operator']) || !in_array($filter['filter_operator'], $filterModel->filterOperatorOptions)) {
            throw new FilterException('Filter operator is missing or not allowed.');
        }        
        
        $checkedFilter = $this->checkFilterFields($filter);
        
        if (count($checkedFilter['filterErrors']) > 0) {
            $this->Controller->Session->setFlash(
                __('%s filter(s) ignored due to missing information: "%s"', count($checkedFilter['filterErrors']), implode(', ', $checkedFilter['filterErrors'])), 
                'default',
                array('class' => "message failure")
                );
        }
        
        // all filters were incompelete don't event show the filters
        if (count($checkedFilter['filter']['filter_param']) != 0) {
            $this->Controller->set('urlParams', http_build_query($checkedFilter['filter'])); // To move to the views using filterParams
            $this->Controller->set('filterParams', $checkedFilter['filter']);
        }
        
        return $filterModel->fromFilterToQueryConditions($checkedFilter['filter'], $countryPrefixes);
    }
    
    //Having a function is better to handle key error.
    public function localize($value) {
        if (isset($this->localizedValueLabel[$value])) {
            return $this->localizedValueLabel[$value];
        }
        return __('%s', $value);
    }
    
    public function checkFilterFields($filter)
    {
        $filterErrors           = array();
        $localizedValueLabel    = $this->localizedValueLabel;
        $filter['filter_param'] = array_filter(
            $filter['filter_param'], 
            function($filterParam) use (&$filterErrors, $localizedValueLabel) {
                if (in_array("", $filterParam)) {
                    if ($filterParam[1] == "") {
                        $filterErrors[] = __("first filter field is missing");
                    } else if ($filterParam[2] == "") {
                        $filterErrors[] = $filterParam[1];
                    } else {
                        $filterErrors[] = array($filterParam[1], $filterParam[2]);
                    } 
                    return false;  //will filter out
                }
                return true;   // will keep this filter
            });
        foreach ($filterErrors as &$filterError) {
            if (is_string($filterError)) {
                $filterError = $this->localize($filterError);                
            } else {
                foreach ($filterError as &$item) {
                    $item = $this->localize($item);
                }
                $filterError = implode(' ', $filterError);
            }
        } 
        $filterCheck['filter'] = $filter;
        $filterCheck['filterErrors'] = $filterErrors;
        return $filterCheck;
    }
}
