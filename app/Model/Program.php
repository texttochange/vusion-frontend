<?php
App::uses('AppModel', 'Model');
App::uses('Schedule', 'Model');


class Program extends AppModel
{
    
    public $displayField        = 'name';
    public $hasAndBelongsToMany = 'User';
    
    public $findMethods = array(
        'authorized' => true,
        'count' => true,
        'listByDatabase' => true,
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
                ),
            'notEditable' => array(
                'rule' => array('isNotEditable'),
                'message' => 'This field is read only.',
                'on' => 'update'
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
                ),
            'notEditable' => array(
                'rule' => array('isNotEditable'),
                'message' => 'This field is read only.',
                'on' => 'update'
                )
            ),
        'status' => array(
            'inList' => array(
                'rule' => array('inList', array('running', 'archived')),
                'message' => 'The status can only be running or archived.'),
            )
        );
    
    
    
    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
        $this->Behaviors->load('FilterMongo');
    }
    
    
    #Filter variables and functions
    public $filterFields = array(
        'name' => array(
            'label' => 'program name',
            'operators' => array(
                'start-with' => array(
                    'label' => 'starts with',
                    'parameter-type' => 'text'),
                'equal-to' => array(
                    'label' => 'equal to',
                    'parameter-type' => 'text'),
                'contain' => array(
                    'label' => 'contain',
                    'parameter-type' => 'text'))),
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
        'status' => array(
            'label' => 'status',
            'operators' => array(
                'is' => array(
                    'label' => 'is',
                    'parameter-type' => 'program-status')))
        );
    
    
    public $filterProgramStatusOptions = array(
        'any' => 'any',
        'running' => 'running',
        'archived' => 'archived'
        );
    
    
    public $filterOperatorOptions = array(
        'all' => 'all',
        'any' => 'any'
        );
    
    
    public function isNotEditable($check) 
    {
        if ($this->id != null) {
            $key = key($check);
            $savedProgram = $this->find(
                'first',
                array('conditions' => array('id' => $this->id)));
            if ($check[$key] != $savedProgram['Program'][$key]) {
                return false;
                
            }
            
        }
        return true;
    }
    
    
    public function notInList($check, $list) 
    {
        
        $value = array_values($check);
        if (in_array(strtolower($value[0]), $list)) {
            return false;
        }
        return true;
    }
    
    
    public function fromFilterToQueryCondition($filterParam)
    {     
        $condition = array();
        if ($filterParam[1] == 'country') {
            if ($filterParam[3]) {
                if ($filterParam[2] == 'is') {
                    $condition['country'] = $filterParam[3];
                }
            }
        } elseif ($filterParam[1] == 'shortcode') {
            if ($filterParam[3]) {
                if ($filterParam[2] == 'is') {
                    $condition['shortcode'] = $filterParam[3];
                }
            }
        } elseif ($filterParam[1] == 'name') {
            if ($filterParam[3]) {
                if ($filterParam[2] == 'equal-to') {
                    $condition['name'] = $filterParam[3];
                } elseif ($filterParam[2] == 'start-with') {
                    $condition['name LIKE'] = $filterParam[3]."%"; 
                } elseif ($filterParam[2] == 'contain') {
                    $condition['name LIKE'] = "%".$filterParam[3]."%";
                }
            }
        } elseif ($filterParam[1] == 'status') {
            if ($filterParam[3]) {
                if ($filterParam[2] == 'is') {
                    $condition['status'] = $filterParam[3];
                }
                if ($filterParam[3] == 'any') {
                    $condition = array(); 
                }
            }
        }
        return $condition;
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

    public function _findListByDatabase($state, $query, $results = array()) 
    {
        if ($state === 'before') {
            $query['fields'] = array('name', 'database');
            $list = array("{n}.Program.database", "{n}.Program.name", null);
            list($query['list']['keyPath'], $query['list']['valuePath'], $query['list']['groupPath']) = $list;
            return $query;
        } 

        if (empty($results)) {
            return array();
        }

        return Hash::combine($results, $query['list']['keyPath'], $query['list']['valuePath'], $query['list']['groupPath']);
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
            if (empty($query['conditions'])) {
                # make conditions an array
                $query['conditions'] = array();
            }
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
    
    
    public function archive() 
    {
        $modifier = $this->saveField('status', 'archived', array('validate' => true));
        if ($modifier['Program']['id'] === '0') {
            return false;
        }
        if ($this->data == null) {
            $this->read();
        } 
        $schedule = new Schedule(array('database' => $this->data['Program']['database']));
        $schedule->deleteAll(true, false);
        return true;
    }
    
    
    public function deleteProgram()
    {
        $program = $this->read();
        if (!$this->delete()) {
            return false;
        }
        $mongoDbSource = ConnectionManager::getDataSource('mongo_program_specific');
        $config = $mongoDbSource->config;
        $host = "mongodb://";
        $hostname = $config['host'] . ':' . $config['port'];
        
        if(!empty($config['login'])){
            $host .= $config['login'] .':'. $config['password'] . '@' . $hostname . '/'. $config['database'];
        } else {
            $host .= $hostname;
        }
        $con = new MongoClient($host);
        $db = $con->selectDB($program['Program']['database']);
        $db->drop();
        return true;
    }


    public function afterSave($created, $options) 
    {
        if ($created) {
            $this->ensureProgramDir($this->data);
        }
        return true;
    }


    public function afterDelete()
    {
        $this->deleteProgramDir($this->data);
        return true;
    }   

    public static function getProgramDir($program)
    {
        if (isset($program['Program']['url'])) {
            $programUrl = $program['Program']['url'];
        } else if (is_string($program)) {
            $programUrl = $program;       
        } else {
            throw new Exception("Cannot generate program dir path.");
        }
        return WWW_ROOT . "files/programs/". $programUrl;
    }

    public static function getProgramDirImport($program)
    {
        $programDir = Program::getProgramDir($program);
        return $programDir . "/imported";
    }


    public static function ensureProgramDir($program)
    {
        $programDirPath = Program::getProgramDir($program);
        Program::ensureDir($programDirPath, True);
        $programDirPathImported = Program::getProgramDirImport($program);
        Program::ensureDir($programDirPathImported);
        return $programDirPath;
    }


    public static function ensureProgramDirImported($program)
    {
        $programDirPath = Program::getProgramDir($program);
        Program::ensureDir($programDirPath, True);
        $programDirPathImported = Program::getProgramDirImport($program);
        Program::ensureDir($programDirPathImported);
        return $programDirPathImported;
    }


    public static function deleteProgramDir($program)
    {
        Program::deleteDir(Program::getProgramDirImport($program));
        Program::deleteDir(Program::getProgramDir($program));
    }

    public static function deleteDir($dir)
    {
        if (file_exists($dir)) {
            $files = glob($dir . '/*'); // get all file names
            foreach($files as $file){ // iterate files
              if(is_file($file))
                unlink($file); // delete file
            }
            rmdir($dir);
        }
    }


    public static function ensureDir($dirPath, $backendAccess=False)
    {
        if (!file_exists($dirPath)) {
            mkdir($dirPath); 
            if (!$backendAccess) {
                chgrp($dirPath, Configure::read('vusion.backendUser'));
                chmod($dirPath, 0774);
            }
        }
        return true;
    }
    
    public static function matchProgramConditions($programDetails, $conditions=array())
    {
        if ($conditions == array()) {
            return true;
        }
        ## AND
        if (isset($conditions['$and'])) {
            foreach ($conditions['$and'] as $subConditions) {
                reset($subConditions);
                $key = key($subConditions);
                if (!Program::validProgramCondition($programDetails, $key, $subConditions[$key])) {
                    return false; #one condition is false => AND failed
                }
            }
            return true; #all condition are true => AND succeed
        }    
        ## OR
        if (isset($conditions['$or'])) {
            foreach ($conditions['$or'] as $subConditions) {
                reset($subConditions);
                $key = key($subConditions);
                if (Program::validProgramCondition($programDetails, $key, $subConditions[$key])) {
                    return true; #one condition is true => OR succeed
                }
            }
            return false; #all conditions are false => OR failed
        } 
        ## only one condition
        reset($conditions);
        $key = key($conditions);
        return Program::validProgramCondition($programDetails, $key, $conditions[$key]);
    }
    
    
    public static function startsWith($haystack, $needle)
    { 
        $length = strlen($needle);
        return (substr(strtolower($haystack), 0, $length) === strtolower($needle));
    }
    
    
    public static function validProgramCondition($programDetails, $conditionKey, $conditionValue)
    {
        switch ($conditionKey) {
        case 'name LIKE':
            if (preg_match("/^%.*%$/i", $conditionValue)) {
                return true;
            } 
            
            if (preg_match("/%+$/i", $conditionValue)) {
                return Program::startsWith($programDetails['Program']['name'],trim($conditionValue, '%'));        
            }
        default:
            if (!isset($programDetails['Program'][$conditionKey])) {
                return false;
            }
            return strcasecmp($programDetails['Program'][$conditionKey], $conditionValue) == 0;
        }
    }
    
    
}
