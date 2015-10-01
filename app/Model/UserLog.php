<?php
App::uses('MongoModel', 'Model');
App::uses('VusionConst', 'Lib');

class UserLog extends MongoModel
{
    
    var $name     = 'UserLog';
    var $useTable = 'user_logs';
    
    var $objectTypes = array('program-user-log', 'vusion-user-log');
    
    var $userLogDefaultFields = array(
        'timestamp',
        'timezone',
        'user-name',
        'user-id',
        'controller',
        'action',
        'parameters');
    
    var $programUserLogExtraFields = array(
        'program-name',
        'program-database-name',            
        );
    
    
    function getModelVersion()
    {
        return '1';
    }
    
    
    function getRequiredFields($object)
    {
        $fields = $this->userLogDefaultFields;
        
        if (in_array($object, array('program-user-log'))) {        
            $fields = array_merge($fields, $this->programUserLogExtraFields);        
        }
        
        return $fields;        
    }


    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
        $this->Behaviors->load('FilterMongo');
    }


    public $validate = array(
        'timestamp' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'The timestamp cannot be empty.'
                ),
            'validateDate' => array(
                'rule' => array('custom', VusionConst::DATE_TIME_REGEX),
                'message' => VusionConst::DATE_TIME_FAIL_MESSAGE,
                'required' => false,
                ),
            ),
        'timezone' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'The timezone cannot be empty.'
                ),
            ),
        'user-name' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'The user name cannot be empty.'
                ),
            ),
        'user-id' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'The user id cannot be empty.'
                ),
            ),
        'object-type' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'The Object type cannot be empty.'
                ),
            'validateObjectType' => array(
                'rule' => 'validateObjectType',
                'message' => 'Object type is invalid.'
                ),
            ),
        'controller' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'The controller cannot be empty.'
                ),
            ),
        'action' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'The action cannot be empty.'
                ),
            ),
        'parameters' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'The parameters cannot be empty.'
                ),
            ),
        'program-name' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'The program name cannot be empty.'
                ),
            ),
        'program-database-name' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'The program database name cannot be empty.'
                ),
            )    
        );
    
    
    public function validateObjectType($object)
    {
        if (in_array($object['object-type'], $this->objectTypes)) {
            return true;
        }
        return false;
    }

    public $filterOperatorOptions = array(
        'all' => 'all',
        'any' => 'any'
        );

    public $filterFields = array(
        'timestamp' =>  array(
            'label' => 'timestamp',
            'operators' => array(
                'from' => array(
                    'parameter-type' => 'date'),
                'to' => array(
                    'parameter-type' => 'date')),
            ),
        'user-id' => array(
            'label' => 'user name',
            'operators' => array(
                'is' => array(
                    'parameter-type' => 'user'),
                'not-is' => array(
                    'parameter-type' => 'user')),
            ),
        'program' => array(
            'label' => 'program',
            'operators' => array(
                'is' => array(
                    'parameter-type' => 'program'))
            ),
        );

    public function fromFilterToQueryCondition($filterParam) 
    {
        $condition = array();
      
        if ($filterParam[1] == 'timestamp') {
            if ($filterParam[3]) {
                if ($filterParam[2] == 'from') { 
                    $condition['timestamp']['$gt'] = DialogueHelper::ConvertDateFormat($filterParam[3]);
                } elseif ($filterParam[2] == 'to') {
                    $condition['timestamp']['$lt'] = DialogueHelper::ConvertDateFormat($filterParam[3]);
                }
            } else {
                $condition['timestamp'] = '';
            }
        } elseif ($filterParam[1] == 'user-id') {
            if ($filterParam[3]) {
                if ($filterParam[2] == 'is') { 
                    $condition['user-id'] = $filterParam[3];
                } elseif ($filterParam[2] == 'to') {
                    $condition['user-id'] = $filterParam[3];
                }
            } else {
                $condition['user-id'] = '';
            }
        } elseif ($filterParam[1] == 'program') {
            if ($filterParam[3]) {
                if ($filterParam[2] == 'is') { 
                    $condition['program-database-name'] = $filterParam[3];
                } elseif ($filterParam[2] == 'to') {
                    $condition['program-database-name'] = $filterParam[3];
                }
            } else {
                $condition['program-database-name'] = '';
            }
        }

        return $condition;
    }
}
