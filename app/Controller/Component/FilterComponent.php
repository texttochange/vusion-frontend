<?php
App::uses('Component', 'Controller');

class FilterComponent extends Component 
{
   
    
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
            //$filterErrors = $this->replaceValue($checkedFilter['filterErrors']);
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
   
    
    public function checkFilterFields($filter)
    {
        $filterErrors           = array();
        $filter['filter_param'] = array_filter(
            $filter['filter_param'], 
            function($filterParam) use (&$filterErrors) {
                if (in_array("", $filterParam)) {
                    if (!function_exists('localizedValue')) {
                        function localizedValue(&$filterParamValue)
                        {
                            $t = array(
                                'not-with' => __('not with'),
                                'start-with' => __('start with'),
                                'phone' => __('phone'),
                                'tagged' => __('tagged'));
                            
                            $localizedLabel = $t[$filterParamValue];
                            return $localizedLabel;        
                        }
                    }
                    if ($filterParam[1] == "") {
                        $filterErrors[] = __("first filter field is missing");
                    } else if ($filterParam[2] == "") {
                        $filterErrors[] = localizedValue($filterParam[1]);
                    } else {
                        //$filterErrors[] = $filterParam[1]." ".$filterParam[2];
                        $filterErrors[] = localizedValue($filterParam[1])." ".localizedValue($filterParam[2]);
                    } 
                    return false;  //will filter out
                }
                return true;   // will keep this filter
            });
        
        $filterCheck['filter'] = $filter;
        $filterCheck['filterErrors'] = $filterErrors;
        return $filterCheck;
    }
    
    
    /*public function localizedValue(&$filterParam)
    {
        $t = array(
            'not-with' => __('not with'),
            'start-with' => __('start with'),
            'phone' => __('phone'),
            'tagged' => __('tagged'));
        
        $localizedLabel = $t[$filterParam];
        return $localizedLabel;        
    }*/
    
}
