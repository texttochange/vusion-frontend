<?php
App::uses('Component', 'Controller');
App::uses('Program', 'Model');
App::uses('ProgramSetting', 'Model');

class FilterComponent extends Component 
{
    public function checkFilterFields($filter)
    {
        $filterErrors           = array();
        $filter['filter_param'] = array_filter(
            $filter['filter_param'], 
            function($filterParam) use (&$filterErrors) {
                if (in_array("", $filterParam)) {
                    if ($filterParam[1] == "") {
                        $filterErrors[] = "first filter field is missing";
                    } else if ($filterParam[2] == "") {
                        $filterErrors[] = $filterParam[1];
                    } else {
                        $filterErrors[] = $filterParam[1]." ".$filterParam[2];
                    } 
                    return false;  //will filter out
                }
                return true;   // will keep this filter
            });
        
        $filterCheck['filter'] = $filter;
        $filterCheck['filterErrors'] = $filterErrors;
         
         //print_r($filter);
        return $filterCheck;
        
    }
    
}
