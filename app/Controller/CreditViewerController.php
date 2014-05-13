<?php
App::uses('AppController', 'Controller');
App::uses('DialogueHelper', 'Lib');
App::uses('Shortcode', 'Model');
App::uses('CreditLog', 'Model');
App::uses('Program', 'Model');


class CreditViewerController extends AppController
{
    var $helpers = array(
        'Js' => array('Jquery'), 
        'Time', 
        'PhoneNumber');
    var $components = array(
        'ProgramPaginator',
        'CreditManager',
        'RequestHandler',
        'LocalizeUtils',
        'PhoneNumber');
    
    var $uses = array(
        'Program', 
        'Group');
    
    
    public function beforeFilter()
    {    
        parent::beforeFilter();
    }
    
    
    public function constructClasses()
    {
        parent::constructClasses();
        
        if (!Configure::read("mongo_db")) {
            $options = array(
                'database' => 'vusion'
                );
        } else {
            $options = array(
                'database' => Configure::read("mongo_db")
                );
        }
        $this->ShortCode        = new ShortCode($options);
        $this->CreditLog        = new CreditLog($options);
    }
    
    
    public function index()
    {        
        $timeframeParameters = $this->_getTimeframeParameters();
        if (!is_array($timeframeParameters)) {
            $this->Session->setFlash($timeframeParameters);
        }

        $conditions = CreditLog::fromTimeframeParametersToQueryConditions($timeframeParameters);
        $countriesCredits   = $this->_getAllCredits($conditions);
        $this->set(compact('countriesCredits'));
    }    

    
    protected function _getAllCredits($conditions)
    {
        $countriesByPrefixes = $this->PhoneNumber->getCountriesByPrefixes();
        $countriesCredits = $this->CreditLog->calculateCreditPerCountry($conditions, $countriesByPrefixes);
        foreach ($countriesCredits as &$countryCredits) {
            foreach ($countryCredits['codes'] as &$codeCredits) {
                $codeCredits['code'] = DialogueHelper::fromPrefixedCodeToCode($codeCredits['code']);
                foreach ($codeCredits['programs'] as &$programCredits) {
                    $program = $this->Program->find('first', array('conditions' => array('database' => $programCredits['program-database'])));
                    $programCredits['name'] = $program['Program']['name'];
                }
            }
        }
        return $countriesCredits;
    }


    protected function _getTimeframeParameters()
    {
        $parameters = array_intersect_key($this->params['url'], array_flip(array('date-from', 'date-to', 'predefined-timeframe')));

        //Default parameters
        if ($parameters == array()) {
            $parameters = array(
                'predefined-timeframe' => 'current-month',
                'date-from' => '',
                'date-to' => '');
        }
        //Create missing key
        $parameters = array_merge(
            array(
                'predefined-timeframe' => '',
                'date-from' => '',
                'date-to' => ''),
            $parameters);

        if ($parameters['predefined-timeframe'] != '' && ($parameters['date-to']!= '' || $parameters['date-from']!= '')) {
                return __("Please select between date or predefine timefame");
            }
       
        
        $this->set('timeframeParams', $parameters);        

        return $parameters;
    }
    
 /*  
    /*    var $filterOperatorOptions = array('all' => 'all');
    var $filterFields = array(
            'country' => array(
                'label' => 'country',
                'operators' => array(
                    'is' => array(
                        'label' => 'is',
                        'parameter-type' => 'country'))),
            'shortcode' => array(
                'label' => 'shortcode',
                'operators' => array(
                    'is' => array(
                        'label' => 'is',
                        'parameter-type' => 'shortcode'))),
            'name' => array(
                'label' => 'program name',
                'operators' => array(
                    'start-with' => array(
                        'label' => 'starts with',
                        'parameter-type' => 'text'),
                    'equal-to' => array(
                        'label' => 'equal to',
                        'parameter-type' => 'text'))),
            'date' => array(
                'label' => 'date',
                'operators' => array(
                    'from' => array(
                        'label' => 'since',
                        'parameter-type' => 'date'),
                    'to' => array(
                        'label' => 'until',
                        'parameter-type' => 'date')))
        );

    protected function _getCredits($conditions)
    {
        if ($programs == array()) {
            return $programs;
        }
        
        $databases = array();
        $shortcodes = array();
        foreach($programs as $program) {
            $databases[] = $program['Program']['database'];
            if (isset($program['Program']['prefixed-shortcode'])) {
                $shortcodes[] = $program['Program']['prefixed-shortcode'];
            }
        }

        $creditLogs = $this->CreditLog->calculateCredits($databases, $shortcodes, $conditions);

        foreach($programs as &$program) {
            $key = CreditLog::searchCreditLog($creditLogs, $program['Program']['database']);
            if ($key!==false) {
                $program['Program']['credit-logs'] = $creditLogs[$key];
            }
        }

        return $programs;
    }


    
    protected function _filterFiltersOnFields($filters, $fields) 
    {
        if (!isset($filters) || !isset($filters['filter_params'])) {
            return array();
        }
        $filters['filter_param'] = array_filter(
            $filters['filter_param'], function ($var) use ($fields) {
                return in_array($var[1], $fields);
            });
        return $filters;
    }


    protected function _fromFilterToProgramConditions($filters)
    {
        if (!isset($filter))
            return array();
        
        //$nonDateConditions = array();
        
        foreach ($filter['filter_param'] as $filterParam) {
            $condition = $this->_fromFilterToProgramConditions($filterParam);
            if (isset($condition)) 
                array_push($nonDateConditions, $condition);
        }
        
        if (count($nonDateConditions) == 1) {
            $nonDateConditions = $nonDateConditions[0];
        } elseif (count($nonDateConditions) > 1) {
            $result['$and'] = $nonDateConditions;
            $nonDateConditions = $result;
        }
        return $nonDateConditions;
    }

    
    protected function _fromFilterToHistoryConditions($filters)
    {
        if (empty($conditions))
            return array();
        
        $result = array();
        foreach ($conditions as $key => $value) {
            if (is_array($value)) {
                if (in_array('timestamp', array_keys($value), true)) {
                    array_push($result, $value);
                } else {
                    $result = array_merge($result, $this->_getDateCondition($value));
                }
            }
        }
        if (count($result) > 0 && !isset($result['$and'])) {
            $newResult['$and'] = $result;
            $result = $newResult;
        }

        return $result;
    }
    

    #Same as ProgramController... need to DRY    
    protected function _getFilterFieldOptions()
    {   
        return $this->LocalizeUtils->localizeLabelInArray($this->Program->filterFields);
    }
    

    #Same as ProgramController... need to DRY
    protected function _getFilterParameterOptions()
    {
        $shortcodes = $countries = array();
        $codes = $this->ShortCode->find('all');
        if (!empty($codes)) {
            foreach ($codes as $code) {
                $shortcodes[$code['ShortCode']['shortcode']] = $code['ShortCode']['international-prefix']."-".$code['ShortCode']['shortcode'];
                $countries[] = $code['ShortCode']['country'];
            }
        }
        sort($countries);
        return array(
            'operator' => $this->Program->filterOperatorOptions,
            'shortcode' => (count($shortcodes)>0? $shortcodes : array()),
            'country' => (count($countries)>0? array_combine($countries, $countries) : array())
            );
    }

 
    ##TODRY: Same a Program Controller
    protected function _getFilters()
    {
        $filters = array_intersect_key($this->params['url'], array_flip(array('filter_param', 'filter_operator')));

        if (!isset($filters['filter_param']))
            return null;
        
        if (!isset($filters['filter_operator']) || !in_array($filters['filter_operator'], $this->Program->filterOperatorOptions)) {
            throw new FilterException(__('Filter operator %s is missing or not allowed.', $filters['filter_operator']));
        }
        
        $this->set('urlParams', http_build_query($filters));
        
        return $filters;
    }


    protected function _fromFilterToQueryConditions($filter)
    {
        if (!isset($filter))
            return array();
        
        $conditions = array();
        
        foreach ($filter['filter_param'] as $filterParam) {
            
            $condition = null;
            
            $this->validateFilter($filterParam);
            
            if ($filterParam[1] == 'date') {
                if ($filterParam[2] == 'from') { 
                    $condition['timestamp']['$gt'] = DialogueHelper::ConvertDateFormat($filterParam[3]);
                } elseif ($filterParam[2] == 'to') {
                    $condition['timestamp']['$lt'] = DialogueHelper::ConvertDateFormat($filterParam[3]);
                }
            } else {
                $condition = $this->_fromFilterToNonDateConditions($filterParam);
            }
            
            if ($filter['filter_operator'] == "all") {
                if (count($conditions) == 0) {
                    $conditions = $condition;
                } elseif (!isset($conditions['$and'])) {
                    $conditions = array('$and' => array($conditions, $condition));
                } else {
                    array_push($conditions['$and'], $condition);
                }
            }  elseif ($filter['filter_operator'] == "any") {
                if (count($conditions) == 0) {
                    $conditions = $condition;
                } elseif (!isset($conditions['$or'])) {
                    $conditions = array('$or' => array($conditions, $condition));
                } else {
                    array_push($conditions['$or'], $condition);
                }
            }
        }
        return $conditions;
    }
    
    
    protected function _fromFilterToNonDateConditions($filterParam)
    {
        $condition = null;
        
        if ($filterParam[1] == 'country') {
            if ($filterParam[2] == 'is') {
                $condition['country'] = $filterParam[3];
            }
        } elseif ($filterParam[1] == 'shortcode') {
            if ($filterParam[2] == 'is') {
                $condition['shortcode'] = $filterParam[3];
            }
        } elseif ($filterParam[1] == 'name') {
            if ($filterParam[2] == 'equal-to') {
                $condition['name'] = $filterParam[3];
            } elseif ($filterParam[2] == 'start-with') {
                $condition['name LIKE'] = $filterParam[3]."%"; 
            }
        }
        
        return $condition;
    }
    
    
    public function validateFilter($filterParam)
    {
        if (!isset($filterParam[1])) {
            throw new FilterException("Field is missing.");
        }
        
        if (!isset($this->filterFields[$filterParam[1]])) {
            throw new FilterException("Field '".$filterParam[1]."' is not supported.");
        }
        
        if (!isset($filterParam[2])) {
            throw new FilterException("Operator is missing for field '".$filterParam[1]."'.");
        }
        
        if (!isset($this->filterFields[$filterParam[1]]['operators'][$filterParam[2]])) {
            throw new FilterException("Operator '".$filterParam[2]."' not supported for field '".$filterParam[1]."'.");
        }
        
        if (!isset($this->filterFields[$filterParam[1]]['operators'][$filterParam[2]]['parameter-type'])) {
            throw new FilterException("Operator type missing '".$filterParam[2]."'.");
        }
        
        if ($this->filterFields[$filterParam[1]]['operators'][$filterParam[2]]['parameter-type'] != 'none' && !isset($filterParam[3])) {
            throw new FilterException("Parameter is missing for field '".$filterParam[1]."'.");
        }
        
        // for date filters
        if (isset($filterParam[1])and $filterParam[1] == 'date') {
            if ((isset($filterParam[2])and $filterParam[2] == 'from') and empty($filterParam[3]))
                throw new FilterException("'Date From' value is missing.");
            if ((isset($filterParam[2])and $filterParam[2] == 'to') and empty($filterParam[3]))
                throw new FilterException("'Date To' value is missing.");
        }
    }
*/    
    
}
