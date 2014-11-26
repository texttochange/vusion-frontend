<?php
App::uses('MongoModel', 'Model');
App::uses('VusionConst', 'Lib');

class UserLog extends MongoModel
{
   
    var $name        = 'UserLog';
    var $useTable    = 'user_logs';  
    
    
    function getModelVersion()
    {
        return '1';
    }

    
    function getRequiredFields($objectType = null)
    {
        return array (
            'timestamp',
            'timezone',
            'user-name',
            'user-id',
            'program-name',
            'program-database-name',
            'controller',
            'action',
            'parameters'
            );    
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
        'program-name' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'The program name cannot be empty.'
                ),
            ),
        'program-database-name' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'The database name cannot be empty.'
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
    
    
}
