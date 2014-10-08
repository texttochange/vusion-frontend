<?php
App::uses('MongoModel', 'Model');

class UserLog extends MongoModel
{
    
    var $specific    = true;
    var $name        = 'UserLog';
    var $useDBConfig = 'mongo';
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
    
    
    public function getUserLogs()
    {
     $userLogs = $this->find('all');
     return $userLogs;
    }

}
