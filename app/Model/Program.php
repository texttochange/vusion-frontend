<?php
App::uses('AppModel', 'Model');
App::uses('History', 'Model');


class Program extends AppModel
{
    
    public $displayField     = 'name';
    var $hasAndBelongsToMany = 'User';
    
    public $findMethods = array(
        'authorized' => true,
        'count' => true
        );
    
    public $validate = array(
        'name' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Please give a name to the program.'
                ),
            'unique' => array(
                'rule' => 'isunique',
                'required' => true,
                'message' => 'Another program is currently using this name, please choose another one.'
                ),
            ),
        'url' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Please give a url to the program.'
                ),
            'unique' => array(
                'rule' => 'isunique',
                'required' => true,
                'message' => 'Another program is currently using this url, please choose another one.'
                ),
            'lowAlphaNumeric'=> array(
                'rule' => array('custom','/^[a-z0-9]{3,}$/'),
                'message' => 'Minimum of 3 characters, can only be composed of lowercase letters and digits.'
                ),
            'notInList' => array(
                'rule' => array('notInList', array('test','groups', 'users', 'admin', 'shortcodes', 'templates',  'programs', 'files', 'js', 'css', 'img')),
                'message' => 'This url is not allowed to avoid overwriting a static Vusion url, please choose a different one.'
                )
            ),
        'database' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Please give a database to the program.'
                ),
            'unique' => array(
                'rule' => 'isunique',
                'required' => true,
                'message' => 'Another program is currently using this database, please choose another one.'
                ),
            'lowAlphaNumeric'=> array(
                'rule' => array('custom','/^[a-z0-9]{3,}$/'),
                'message' => 'Minimum of 3 characters, can only be composed of lowercase letters and digits.'
                ),
            'notInList' => array(
                'rule' => array('notInList', array('test', 'vusion')),
                'message' => 'This database name is not allowed to avoid overwriting a static Vusion database, please choose a different one.'
                )
            )
        );
    
    #Filter variables and functions
    public $filterFields = array(
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
                    'parameter-type' => 'text')))
        );
    
    public $filterOperatorOptions = array(
        'all' => 'all',
        'any' => 'any'
        );
    
    
    public function notInList($check, $list) {
        
        $value = array_values($check);
        if (in_array(strtolower($value[0]), $list)) {
            return false;
        }
        return true;
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
    
    
    public function fromFilterToQueryConditions($filter)
    {
        $conditions = array();
        
        foreach ($filter['filter_param'] as $filterParam) {
            
            $condition = null;
            
            $this->validateFilter($filterParam);
            
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
    
    
    public function _findAuthorized($state, $query, $results = array())
    {
        if ($state == 'before') {
            return $this->limitedAccessConditions($query);
        }
        return $results;
    }
    
    
    public function _findCount($state, $query, $results = array())
    {
        if ($state === 'before') {
            $db = $this->getDataSource();
            if (empty($query['fields'])) {
                $query['fields'] = $db->calculate($this, 'count');
            } elseif (is_string($query['fields'])  && !preg_match('/count/i', $query['fields'])) {
                $query['fields'] = $db->calculate($this, 'count', array(
                    $db->expression($query['fields']), 'count'
                    ));
            }
            $query['order'] = false;
            
            return $this->limitedAccessConditions($query);
        } elseif ($state === 'after') {
            foreach (array(0, $this->alias) as $key) {
                if (isset($results[0][$key]['count'])) {
                    if (($count = count($results)) > 1) {
                        return $count;
                    } else {
                        return intval($results[0][$key]['count']);
                    }
                }
            }
            return false;
        }
    }
    
    
    protected function limitedAccessConditions($query)
    {
        if (isset($query['specific_program_access']) and $query['specific_program_access']) {
            $query['joins'] = array(
                array(
                    'table' => 'programs_users',
                    'alias' => 'ProgramUser',
                    'type' => 'LEFT',
                    'conditions' => array(
                        'Program.id = ProgramUser.program_id'
                        )
                    )
                );
            if (empty($query['conditions']))
                # make conditions an array
            $query['conditions'] = array();
            # append user_id to conditions array
            $query['conditions'] = array_merge(
                $query['conditions'],array(
                    'ProgramUser.user_id' => $query['user_id']
                    ));
            if (!empty($query['program_url'])) {
                $query['conditions'] = array_merge(
                    $query['conditions'],
                    array('Program.url' => $query['program_url'])
                    ); 
            }
        } else {
            if (!empty($query['program_url'])) {
                $query['conditions'] = array('Program.url' => $query['program_url']);
            }
        }
        return $query;
    }
    
    
    public function deleteProgram()
    {
        $program = $this->read();
        if (!$this->delete()) {
            return false;
        }
        $mongoDbSource = ConnectionManager::getDataSource('mongo');
        $config = $mongoDbSource->config;
        $host = "mongodb://";
        $hostname = $config['host'] . ':' . $config['port'];
        
        if(!empty($config['login'])){
            $host .= $config['login'] .':'. $config['password'] . '@' . $hostname . '/'. $config['database'];
        } else {
            $host .= $hostname;
        }
        $con = new Mongo($host);
        $db = $con->selectDB($program['Program']['database']);
        $db->drop();
        return true;
    }
    
    
    public function matchProgramByShortcodeAndCountry($program, $conditions, $codes)
    {
        $result = array();
        $countryMatch = false;
        $shortcodeMatch = false;
        
        if (empty($codes))
            return array();
        
        foreach ($codes as $code) {
            if (isset($conditions['$and'])) { 
                $conditionHas = $this->_conditonHasCountryAndShortcodeOptions($conditions);
                if ($conditionHas['country'] == 1 and $conditionHas['shortcode'] == 1) {
                    foreach ($conditions['$and'] as $key => $value) {
                        if (!is_array($value))
                            return;
                        
                        foreach ($value as $key2 => $value2) {
                            if($key2 == 'country') {
                                if (strtolower($value2) == strtolower($code['country'])) {
                                    $countryMatch = true;
                                }
                            }
                            if($key2 == 'shortcode') {
                                if ($value2 == $code['shortcode']) {
                                    $shortcodeMatch = true;
                                }
                            }
                            
                            if ($shortcodeMatch == true && $countryMatch == true) {
                                array_push($result, $program);
                            }
                        }
                    }
                    #re-initialize countryMatch and shortcodeMatch to prevent duplicate results
                    $countryMatch = false;
                    $shortcodeMatch = false;
                }
                elseif ($conditionHas['country'] == 1) {
                    foreach ($conditions['$and'] as $key => $value) {
                        if (isset($value['country']))
                            if (strtolower($value['country']) == strtolower($code['country']))
                            array_push($result, $program);
                    }
                }
                elseif ($conditionHas['shortcode'] == 1) {
                    foreach ($conditions['$and'] as $key => $value) {
                        if (isset($value['shortcode']))
                            if ($value['shortcode'] == $code['shortcode'])
                            array_push($result, $program);
                    }
                } else {
                    # Do Nothing
                }
            } elseif (isset($conditions['$or'])) {
                foreach ($conditions['$or'] as $key => $value) {
                    if (is_array($value)) {
                        if (isset($value['country'])) {
                            if (strtolower($value['country']) == strtolower($code['country'])) {
                                array_push($result, $program);                                
                            }
                        }
                        if (isset($value['shortcode'])) {
                            if ($value['shortcode'] == $code['shortcode']) {
                                array_push($result, $program);
                            }
                        }
                    }
                }                
            } else {
                if (isset($conditions['country'])) {
                    if (strtolower($conditions['country']) == strtolower($code['country'])) {
                        array_push($result, $program);
                    }
                } elseif (isset($conditions['shortcode'])) {
                    if ($conditions['shortcode'] == $code['shortcode']) {
                        array_push($result, $program);
                    }
                }
            }
        }
        return $result;
    }
    
    
    protected function _conditonHasCountryAndShortcodeOptions($conditions)
    {
        $conditionHas = array('country' => 0, 'shortcode' => 0);
        if (isset($conditions['$and'])) {                
            foreach ($conditions['$and'] as $key => $value) {
                if (is_array($value)) {
                    $test = array_keys($value);
                    if (in_array('country',$test))
                        $conditionHas['country'] = 1;
                    if (in_array('shortcode',$test))
                        $conditionHas['shortcode'] = 1;
                }
            }
        }
        return $conditionHas;
    }
    
    
}
