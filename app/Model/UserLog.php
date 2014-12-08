<?php
App::uses('MongoModel', 'Model');
App::uses('VusionConst', 'Lib');

class UserLog extends MongoModel
{
   
    var $name        = 'UserLog';
    var $useTable    = 'user_logs';  
    
    var $programSpecificType = array('program-user-log');
    
    function getModelVersion()
    {
        return '1';
    }

    
    function getRequiredFields($object)
    {
        $fields = array(
            'timestamp',
            'timezone',
            'user-name',
            'user-id',
            'controller',
            'action',
            'parameters');
        
        $PROGRAMSPECIFIC_FIELDS = array(
            'program-name',
            'program-database-name',            
            );
        
        if (in_array($object, $this->programSpecificType)) {        
            $fields = array_merge($fields, $PROGRAMSPECIFIC_FIELDS);        
        }
        
        return $fields;        
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
            )        
        );
    
    
    public function validateObjectType($object)
    {
        if ($object['object-type'] == 'program-user-log' || $object['object-type'] == 'vusion-user-log') {
            return true;
        }
        return false;
    }
    
    
}
