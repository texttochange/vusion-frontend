<?php
App::uses('AppController', 'Controller');
App::uses('Program', 'Model');
App::uses('DialogueHelper', 'Lib');

class CreditViewerController extends AppController
{
    var $helpers = array('Js' => array('Jquery'), 'Time', 'PhoneNumber');
    var $components = array('ProgramPaginator', 'CreditManager', 'RequestHandler', 'LocalizeUtils', 'PhoneNumber');
    
    var $filterOperatorOptions = array('all' => 'all', 'any' => 'any');
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
        $this->Program          = new Program();
        $this->DialogueHelper   = new DialogueHelper();
    }
    
    
    public function index()
    {
        $this->set('filterFieldOptions', $this->_getFilterFieldOptions());
        $this->set('filterParameterOptions', $this->_getFilterParameterOptions());
        
        $defaultDateConditions = array(
            1 => array(1=>'date', 2 => 'from', 3 => ''),
            2 => array(1=>'date', 2 => 'to', 3 => '')
        );
        $this->set('defaultDateConditions', $defaultDateConditions);
        
        $conditions = $this->_getConditions();
        
        $conditionsNonDate = $this->_getNonDateConditions($this->_getFilter());        
        $nonDateConditions = (isset($conditionsNonDate)) ? $conditionsNonDate : array();
        
        $nameCondition = $this->ProgramPaginator->getNameSqlCondition($conditions);
        
        $programs    =  $this->Program->find('all', array(
            'conditions' => $nameCondition,
            'order' => array(
                'Program.created' => 'desc'
                ))
            );
        
        $allPrograms = $this->Program->find('all');

        if (isset($conditions['$or']) and !isset($nameCondition['OR']))
            $programsList =  $allPrograms;
        else
            $programsList =  $programs;
            
        $filteredPrograms = array();

        foreach ($programsList as &$program) {
            $progDetails = $this->ProgramPaginator->getProgramDetails($program);
            $program = array_merge($program, $progDetails['program']);
            if ($this->params['url'] == array()) {
                $program['Program']['total-credits'] = $this->CreditManager->getCount($program['Program']['database']);
            } else {
                $program['Program']['total-credits'] = $this->_getCreditsFromProgramHistory($program['Program']['database'], $conditions);
            }
            $filterPrograms = $this->Program->matchProgramByShortcodeAndCountry(
                $progDetails['program'],
                $nonDateConditions,
                $progDetails['shortcode']);
            if (count($filterPrograms)>0) {
                foreach ($filterPrograms as $fProgram) {
                    $fProgram['Program']['total-credits'] = $this->_getCreditsFromProgramHistory($fProgram['Program']['database'], $conditions);
                    $filteredPrograms[] = $fProgram;
                }
            }
            
        }
        
        if (count($filteredPrograms)>0
            or (isset($conditions) && $nameCondition == array() && $nonDateConditions != array())
            or (isset($conditions['$and']) && $nameCondition != array() && count($filteredPrograms) == 0)) {
            $programsList = $filteredPrograms;
        }
        
        if (isset($conditions['$or']) and !isset($nameCondition['OR']) and $nameCondition != array()) {
            foreach($programs as &$program) {
                $details = $this->ProgramPaginator->getProgramDetails($program);
                $program = array_merge($program, $details['program']);
            }
            foreach ($programsList as $listedProgram) {
                if (!in_array($listedProgram, $programs))
                    array_push($programs, $listedProgram);
            }
        } else {
            $programs = $programsList;
        }

        $programs = $this->ProgramPaginator->paginate($programs);
        $this->set(compact('programs', $programs));
    }
    
    
    protected function _getCreditsFromProgramHistory($dbName, $conditions)
    {
        $this->History = new History(array('database' => $dbName));
        
        $defaultConditions = array(
            '$and' => array(
                array('object-type'=> array(
                    '$in'=> array('dialogue-history','request-history','unattach-history','unmatching-history')
                    )),
                array('message-status' => array(
                    '$nin' => array('missing-data','failed','no-credit','no-credit-timeframe')
                    ))
                )
            );
        $dateConditions = $this->_getDateCondition($conditions);

        if (!empty($dateConditions))
            array_push($defaultConditions['$and'], $dateConditions);

        $messages = $this->History->find(
            'all',
            array('conditions' => $defaultConditions)
            );

        $totalCredits = 0;
        foreach ($messages as $message) {
            $totalCredits += $message['History']['message-credits'];
        }
        
        return (int)$totalCredits;
    }
    
    
    protected function _getDateCondition($conditions)
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
    
    
    protected function _getNonDateConditions($filter)
    {
        if (!isset($filter))
            return array();
        
        $nonDateConditions = array();
        
        foreach ($filter['filter_param'] as $filterParam) {
            $condition = $this->_fromFilterToNonDateConditions($filterParam);
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
    
    
    protected function _getFilterFieldOptions()
    {   
        return $this->LocalizeUtils->localizeLabelInArray($this->filterFields);
    }
    
    
    protected function _getFilterParameterOptions()
    {
        $shortcodes = $countries = array();
        $codes = $this->ShortCode->find('all');
        if (!empty($codes)) {
            foreach ($codes as $code) {
                $shortcodes[] = $code['ShortCode']['shortcode'];
                $countries[] = $code['ShortCode']['country'];
            }
        }
        sort($countries);
        
        return array(
            'operator' => $this->filterOperatorOptions,
            'shortcode' => (count($shortcodes)>0? array_combine($shortcodes, $shortcodes) : array()),
            'country' => (count($countries)>0? array_combine($countries, $countries) : array())
            );
    }
    
    protected function _getFilter()
    {
        $filter = array_intersect_key($this->params['url'], array_flip(array('filter_param', 'filter_operator')));

        if (!isset($filter['filter_param']))
            return null;
        
        if (!isset($filter['filter_operator']) || !in_array($filter['filter_operator'], $this->filterOperatorOptions)) {
            throw new FilterException('Filter operator is missing or not allowed.');
        }
        
        $this->set('urlParams', http_build_query($filter));
        
        return $filter;
    }
    
    
    protected function _getConditions()
    {
        $filter = $this->_getFilter();
        
        return $this->_fromFilterToQueryConditions($filter);
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
                    $condition['timestamp']['$gt'] = $this->DialogueHelper->ConvertDateFormat($filterParam[3]);
                } elseif ($filterParam[2] == 'to') {
                    $condition['timestamp']['$lt'] = $this->DialogueHelper->ConvertDateFormat($filterParam[3]);
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
    }
    
    
}
