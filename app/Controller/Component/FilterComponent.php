<?php
App::uses('Component', 'Controller');


class FilterComponent extends Component 
{

    var $localizedValueLabel = array();
    var $currentQuery = array();

    
    public function startup(Controller $controller)
    {
        parent::startup($controller);
        $this->Controller = $controller;
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
            'are-present' => __('are present'),
            );
        $this->Controller->set('filterLabels', $this->localizedValueLabel);
    }
    
    
    public function getFilters()
    {
        return array_intersect_key($this->Controller->params['url'], array_flip(array('filter_param', 'filter_operator'))); 
    }


    public function getConditions($filterModel, $defaultConditions = array(), $otherModels = array(), $joinCursor = true)
    {       
        $filter = $this->getFilters();
  
        if ($filter == array()) {
            return $defaultConditions;
        }

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
        if (count($checkedFilter['filter']['filter_param']) != 0) {
            // To move to the views using filterParams
            $this->Controller->set('urlParams', http_build_query($checkedFilter['filter'])); 
            $this->Controller->set('filterParams', $checkedFilter['filter']);
        }

        //Run the pre-join request
        $otherModelConditions = array();
        foreach($checkedFilter['joins'] as $join) {
            if ($joinCursor) {
                $results = call_user_func(
                    array($otherModels[$join['model']], $join['function']),
                    $join['parameters']);
            } else {
                $results = $join;
            }
            $otherModelConditions = array(
                $join['field'] => array('$join' => $results));
        }
        $filterConditions = $filterModel->fromFiltersToQueryCondition($checkedFilter['filter'], $otherModelConditions);
        $filterConditions = $filterModel->mergeFilterConditions($defaultConditions, $filterConditions);
        return $filterConditions;
    }

    
    //Having a function is better to handle key error.
    public function localize($value) {
        if (isset($this->localizedValueLabel[$value])) {
            return $this->localizedValueLabel[$value];
        }
        return __('%s', $value);
    }


}
