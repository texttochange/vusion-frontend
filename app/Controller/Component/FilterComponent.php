<?php
App::uses('Component', 'Controller');

class FilterComponent extends Component 
{
    
    var $localizedValueLabel = array();
    var $currentQuery = array();
    
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
            'participant' => __('participant'),
            'tagged' => __('tagged'),
            'labelled' => __('labelled'),
            'optin' => __('optin'),
            'optout' => __('optout'),
            'has-schedule' => __('has schedule'),
            ); 
        $this->Controller = $collection->getController();
        parent::__construct($collection, $settings);
    }
    
    
    public function initialize(Controller $controller)
    {
        parent::startup($controller);
        $this->Controller = $controller;
        $this->Controller->set('filterLabels', $this->localizedValueLabel);
    }
    
    
    public function getConditions($filterModel, $defaultConditions = null, $otherModels = array())
    {       
        $filter = array_intersect_key($this->Controller->params['url'], array_flip(array('filter_param', 'filter_operator')));       
        
        $checkedFilter = $filterModel->validateFilter($filter);
        //Make sure the incorrect filters will be mention in the flash message    
        if (count($checkedFilter['errors']) > 0) {
            $filterErrors = array();
            foreach ($checkedFilter['errors'] as $filterError) {
                if (is_string($filterError)) {
                    $filterErrors[] = $this->localize($filterError);                
                } else {
                    foreach ($filterError as &$item) {
                        $item = $this->localize($item);
                    }
                    $filterErrors[] = implode(' ', $filterError);
                }
            } 
            $this->Controller->Session->setFlash(
                __('%s filter(s) ignored due to missing information: "%s"', 
                    count($checkedFilter['errors']), 
                    implode(', ', $filterErrors)));
        }
        
        //All incomplete filters are deleted
        if (count($checkedFilter['filter']['filter_params']) != 0) {
            // To move to the views using filterParams
            $this->Controller->set('urlParams', http_build_query($checkedFilter['filter'])); 
            $this->Controller->set('filterParams', $checkedFilter['filter']);
        }

        //Run the pre-join request
        $otherModelConditions = array();
        foreach($checkedFilter['joins'] as $join) {
            $results = call_user_func(
                array($otherModels[$join['model']], $join['function']),
                $join['parameters']);
            $otherModelConditions[] = array(
                $join['field'] => array('$join' => $results));
        }
        return $filterModel->fromFiltersToQueryCondition($checkedFilter['filter'], $otherModelConditions);
    }
    
    //Having a function is better to handle key error.
    public function localize($value) {
        if (isset($this->localizedValueLabel[$value])) {
            return $this->localizedValueLabel[$value];
        }
        return __('%s', $value);
    }
    
/*
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
    */
}
